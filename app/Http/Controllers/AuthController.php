<?php

namespace App\Http\Controllers;

use Validator;
use Carbon\Carbon;

use App\Models\Authentication;
use App\Models\Address;
use App\Models\Developer;
use App\Models\SystemEmails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mails\User\verifyEmailAddress;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'check', 'verify']]);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60
        ];
    }

    protected function storeDeveloper(Array $user, Int $user_id)
    {
        $developer_address                 = new Address;
        $developer_address->administrative = ucwords($user['address']['administrative']);
        $developer_address->city           = ucwords($user['address']['city']);
        $developer_address->country        = ucwords($user['address']['country']);
        $developer_address->county         = ucwords($user['address']['county']);
        $developer_address->name           = ucwords($user['address']['name']);
        $developer_address->postcode       = $user['address']['postcode'];
        $developer_address->save();

        $developer             = new Developer;
        $developer->user_id    = $user_id;
        $developer->firstname  = ucfirst($user['firstname']);
        $developer->lastname   = strtoupper($user['lastname']);
        $developer->dob        = $user['dob'];
        $developer->phone      = $user['phone'];
        $developer->address_id = $developer_address->id;
        $developer->save();

        return true;
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user.firstname'              => 'required|alpha_dash',
            'user.lastname'               => 'required|alpha_dash',
            'user.dob'                    => 'required|numeric',
            'user.phone'                  => ['required','regex:/^[+][\d]+$/'],
            'user.address.postcode'       => 'required|numeric',
            'user.address.name'           => ['required',"regex:/^[a-zA-Z’'\- àéè`ÉÈÀ]+$/"],
            'user.address.city'           => ['required',"regex:/^[a-zA-Z’'\- àéè`ÉÈÀ]+$/"],
            'user.address.county'         => ['required',"regex:/^[a-zA-Z’'\- àéè`ÉÈÀ]+$/"],
            'user.address.administrative' => ['required',"regex:/^[a-zA-Z’'\- àéè`ÉÈÀ]+$/"],
            'user.address.country'        => ['required',"regex:/^[a-zA-Z’'\- àéè`ÉÈÀ]+$/"],
            'user.accountType'            => 'required|in:customer,developer',
            'user.email'                  => 'required|email|indisposable|max:70|unique:authentication,email',
            'user.passwords.password'     => 'required|min:6|confirmed'
        ]);

        if($validator->fails()) {
            return json_response('validation_error', '/errors/register#validator-fails', $validator->messages()->all(), null, 422);
        }

        $data = $request->user;
        $user = Authentication::create([
            'email'         => strtolower($data['email']),
            'password'      => bcrypt($data['passwords']['password']),
            'registered_ip' => $request->ip(),
            'last_ip'       => $request->ip()
        ]);
        Mail::to($user)->send(new verifyEmailAddress($user));
        $credentials = ['email' => $data['email'], 'password' => $data['passwords']['password']];
        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if($this->storeDeveloper($data, $user->id)) {
            return json_response('auth_success', null, null, $this->respondWithToken($token), 200);
        } else {
            return json_response('storing_error', '/errors/register#storing-error', 'Erreur lors de l\'enregistrement', null, 500);
        }
    }

    public function login(Request $request)
    {
        $rules = [
            'email'    => 'required|email|max:70',
            'password' => 'required|min:6|max:50'
        ];
        $credentials = $request->only('email', 'password');
        $validator   = Validator::make($credentials, $rules);
        if(!$token = auth()->attempt($credentials)) {
            return json_response('wrong_credentials', '/errors/login#wrong-credentials', [__('auth.failed')], null, 401);
        }
        $user = Authentication::where('email', $credentials['email']);
        if(!$user->first()->email_confirmed) {
            return json_response('email_not_confirmed', '/errors/login#email-not-confirmed', [__('auth.email_not_confirmed')], null, 401);
        }

        $user->update(['last_ip' => $request->ip()]);

        return json_response('auth_success', null, null, $this->respondWithToken($token), 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    public function check()
    {
        if(auth()->user()) {
            return response()->json(['authenticated' => true], 200);
        } else {
            return response()->json(['authenticated' => false], 401);
        }
    }

    public function verify(Request $request, String $code) {
        $checkCode = SystemEmails::where(['code' => $code, 'verified' => false, 'type' => 'verify-email']);

        if(!$checkCode->first()) {
            return json_response('verify_not_found', '/errors/verify#not-found', [__('auth.verify.not-found')], null, 422);
        }
        if(Carbon::now() > $checkCode->first()->expire_at) {
            return json_response('verify_expired', '/errors/verify#expired', [__('auth.verify.expired')], null, 401);
        }

        $checkCode = $checkCode->first();
        $checkCode->verified = true;
        $checkCode->save();
        $user = Authentication::where('id', $checkCode->first()->user_id)->first();
        $user->email_confirmed = true;
        $user->save();

        return json_response('verify_success', null, null, null, 200);
    }
}
