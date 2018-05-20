<?php

namespace App\Http\Controllers;

use App\User;
use App\SendEmail;
use App\AccountSecurity;

use DB;
use Mail;
use JWTAuth;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected function sendAnotherEmail($email, $type)
    {
        $user = User::where('email', $email)->first();
        $data = [
            'uuid' => $user->uuid,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' =>  $user->lastname,
            'code' => str_random(30)
        ];
        $data['verify_url'] = route('auth.verify', ['email' => $data['email'], 'code' => $data['code']]);
        
        $insertVerify = new SendEmail;
        $insertVerify->uuid = $data['uuid'];
        $insertVerify->email = $data['email'];
        $insertVerify->code = $data['code'];
        $insertVerify->type = $type;
        $insertVerify->expire_at = Carbon::now()->addHours(3);
        $insertVerify->save();

        try {
            return Mail::send('emails.verify-email', $data, function($message) use ($data) {
                $message->to($data['email'], $data['firstname']." ".$data['lastname'])->subject("Vérifiez votre adresse email");
            });
        } catch(Exception $e) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => $e->getMessage(),
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 500
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $rules = [
            'firstname' => 'required|regex:/([A-z-])/|min:3|max:20',
            'lastname'  => 'required|regex:/([A-z-])/|min:3|max:20',
            'email'     => 'required|email|max:70|unique:authentication',
            'password'  => 'required|min:6|max:50|confirmed',
        ];
        $data = $request->only('firstname', 'lastname', 'email', 'password', 'password_confirmation');
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => $validator->messages()->all(),
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 422
            ], 422);
        }
        $uid_numb = User::count() + 1;
        $uid_name = strtolower($data['lastname'][0]);
        $uid_fname = strtolower($data['firstname'][0]);
        $uuid = $uid_name.$uid_fname.$uid_numb."-cdev";

        $user = User::create([
            'uuid' => $uuid,
            'firstname' => ucfirst($data['firstname']),
            'lastname' => strtoupper($data['lastname']),
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
            'registered_ip' => \Request::ip(),
            'last_ip' => \Request::ip()
        ]);

        $this->sendAnotherEmail($data['email'], 'verify');

        $token = JWTAuth::fromUser($user);
        return response()->json([
            'endpoint' => '/'.$request->path(),
            'success' => true,
            'timestamp' => Carbon::now()->timestamp,
            'data' => ['token' => $token]
        ], 200);

    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email|max:70',
            'password' => 'required|min:6|max:50'
        ];
        $data = $request->only(
            'email',
            'password'
        );

        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => $validator->messages()->all(),
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 422
            ], 422);
        }
        $countUser = User::where('email', $request->email)->count();
        
        if($countUser == 0) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'no_account_registered_with_email',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 422
            ], 422);
        }

        $getUser = User::where('email', $request->email)->first();
        if($getUser->email_confirmed == 0) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'email_not_confirmed',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 401
            ], 401);
        }
        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
            'email_confirmed' => 1
        ];

        $accountLocked = AccountSecurity::where([['uuid', $getUser->uuid], ['locked', true]])->first();
        
        if($getUser->login_attempt >= 3) {
            if(Carbon::now() >= $accountLocked->until) {
                $accountLocked->delete();
                $getUser->login_attempt = 0;
                $getUser->save();
            }
        }

        try {
            if(!$token = JWTAuth::attempt($credentials)) {
                $getUser->increment('login_attempt');

                if($getUser->login_attempt >= 3) {
                    $lockAccount = new AccountSecurity;
                    $lockAccount->uuid = $getUser->uuid;
                    $lockAccount->locked = true;
                    $lockAccount->until = Carbon::now()->addMinutes(15);
                    $lockAccount->save();

                    $lockedUntil = Carbon::now()->addMinutes(15)->timestamp;
                    return response()->json([
                        'endpoint' => '/'.$request->path(),
                        'success' => false,
                        'errors'  => [
                            'account_locked_due_too_many_attempt',
                            $lockedUntil
                        ],
                        'timestamp' => Carbon::now()->timestamp,
                        'http_code' => 401
                    ], 401);
                }
                return response()->json([
                    'endpoint' => '/'.$request->path(),
                    'success' => false,
                    'errors'  => 'invalid_credentials',
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 401
                ], 401);
            }
        } catch(Exception $e) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => $e->getMessage(),
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 500
            ], 500);
        }
        
        if($accountLocked >= 1) {
            if(Carbon::now() <= $accountLocked->until) {
                return response()->json([
                    'endpoint' => '/'.$request->path(),
                    'success' => false,
                    'errors'  => 'account_locked',
                    'timestamp' => Carbon::now()->timestamp,
                    'http_code' => 401
                ]);
            }
            $accountLocked->delete();
        }
        $token = compact('token');
        $getUser->login_attempt = 0;
        $getUser->save();
        return response()->json([
            'endpoint' => '/'.$request->path(),
            'success' => true,
            'timestamp' => Carbon::now()->timestamp,
            'data' => [$token]
        ], 200);
    }

    public function verify(Request $request, $email, $code)
    {
        
        $verify = SendEmail::where([['code', $code], ['email', $email], ['type', 'verify']])->first();
        
        if(!$verify) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'no_verify_links_with_credentials',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 401
            ], 401);
        }

        if($code !== $verify->code) {
            $this->sendAnotherEmail($email, 'verify');
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'security_token_invalid',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 401
            ], 401);
        } if($email !== $verify->email) {
            $this->sendAnotherEmail($email, 'verify');            
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'email_invalid',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 401
            ], 401);
        } if(Carbon::now() > $verify->expire_at) {
            $this->sendAnotherEmail($email, 'verify');            
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'security_token_expired',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 401
            ], 401);
        } if($verify->verified == 1) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'errors'  => 'email_already_confirmed',
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 422
            ], 422);
        }
        $verify->verified = true;
        $verify->save();
        $user = User::where('email', $email)->first();
        $user->email_confirmed = true;
        $user->save();

        return response()->json([
            'endpoint' => '/'.$request->path(),
            'success' => true,
            'timestamp' => Carbon::now()->timestamp,
            'data' => ['email_verified']
        ], 200);
    }
}
