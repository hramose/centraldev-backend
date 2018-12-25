<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use Validator;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Create a new User.
     */
    public function register(Request $request)
    {
        $rules = [
            'email'     => 'required|email|max:70|unique:authentication',
            'password'  => 'required|min:6|max:50|confirmed',
        ];
        $data = $request->only('email', 'password', 'password_confirmation');
        $validator = Validator::make($data, $rules);
        if($validator->fails()) {
            return response()->json([
                'endpoint' => '/'.$request->path(),
                'success' => false,
                'documentation_url' => 'https://docs.centraldev.fr/errors/register#validator-fails',
                'errors'  => $validator->messages()->all(),
                'timestamp' => Carbon::now()->timestamp,
                'http_code' => 422
            ], 422);
        }
        $user = User::create([
            'email' => strtolower($data['email']),
            'password' => bcrypt($data['password']),
            'registered_ip' => \Request::ip(),
            'last_ip' => \Request::ip()
        ]);
        // $this->sendEmail($data['email'], 'verify');
        // $token = JWTAuth::fromUser($user);
        $credentials = \request(['email', 'password']);
        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}