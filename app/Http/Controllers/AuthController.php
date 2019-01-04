<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mails\User\verifyEmailAddress;

use DB;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'check']]);
    }

    protected function respondWithToken($token)
    {
        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ];
    }

    public function check()
    {
        if(auth()->user()) {
            return response()->json(['authenticated' => true], 200);
        } else {
            return response()->json(['authenticated' => false], 401);
        }
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

    public function register(Request $request)
    {
        $rules = [
            'email'     => 'required|email|max:70|unique:authentication',
            'password'  => 'required|min:6|max:50|confirmed',
        ];
        $data = $request->only('email', 'password', 'password_confirmation');
        $validator = Validator::make($data, $rules);

        if($validator->fails()) {
            return json_response('validation_error', 'https://docs.centraldev.fr/errors/register#validator-fails', $validator->messages()->all(), null, 422);
        }

        $user = User::create([
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
            'registered_ip' => \Request::ip(),
            'last_ip' => \Request::ip()
        ]);
        Mail::to($user)->send(new verifyEmailAddress($user));
        $credentials = \request(['email', 'password']);
        
        if(!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
}