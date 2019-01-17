<?php

namespace App\Http\Controllers;

use DB;
use Validator;
use Carbon\Carbon;

use App\Models\User;
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
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user.email' => 'required|email|max:70|unique:authentication,email',
            'user.passwords.password' => 'required|min:6|confirmed'
        ]);

        if($validator->fails()) {
            return json_response('validation_error', 'https://docs.centraldev.fr/errors/register#validator-fails', $validator->messages()->all(), null, 422);
        }

        $data = $request->user;

        $user = User::create([
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['passwords']['password']),
            'registered_ip' => \Request::ip(),
            'last_ip' => \Request::ip()
        ]);
        Mail::to($user)->send(new verifyEmailAddress($user));
        $credentials = ['email' => $data['email'], 'password' => $data['passwords']['password']];

        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return json_response('auth_success', null, null, $this->respondWithToken($token), 200);
    }

    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|email|max:70',
            'password' => 'required|min:6|max:50'
        ];
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($credentials, $rules);
        if(!$token = auth()->attempt($credentials)) {
            return json_response('wrong_credentials', 'https://docs.centraldev.fr/errors/login#wrong-credentials', [__('auth.failed')], null, 401);
        }
        $user = User::where('email', $credentials['email']);
        if(!$user->first()->email_confirmed) {
            return json_response('email_not_confirmed', 'https://docs.centraldev.fr/errors/login#email-not-confirmed', [__('auth.email_not_confirmed')], null, 401);
        }

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

    public function verify(Request $request, $code) {
        return $code;
    }
}
