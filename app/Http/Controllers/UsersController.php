<?php

namespace App\Http\Controllers;

use JWTAuth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;

class UsersController extends Controller
{
    public function me(Request $request)
    {
        try {
		    if (! $user = JWTAuth::parseToken()->authenticate()) {
			    return response()->json(['user_not_found'], 404);
		    }
	    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
		    return response()->json(['token_expired'], $e->getStatusCode());
	    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
		    return response()->json(['token_invalid'], $e->getStatusCode());
	    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
		    return response()->json(['token_absent'], $e->getStatusCode());
	    }
        JWTAuth::parseToken();
        $user = JWTAuth::parseToken()->authenticate();
        $data = [
            'account_type' => 'customer',
            'address' => [
                'number' => 1,
                'line_1' => 'Wonderland Road',
                'line_2' => 'Appartment 35',
                'zipcode' => '256710',
                'state' => 'California',
                'country' => 'United States of America',
            ],
            'is_company' => true,
            'company' => [
                'size' => '1',
                'role' => 'ceo',
                'name' => 'Central\'DEV',
                'logo' => 'https://example.com/image.png',

            ]
        ];
        return response()->json([$user, $data]);
    }
}
