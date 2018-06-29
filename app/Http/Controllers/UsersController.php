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
                return response()->json([
                    'success' => false,
                    'error' => 'Aucun utilisateur trouvé.'
                ], 404);
            }
	    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
		    return response()->json([
                'success' => false,
                'error' => 'Le token transmis à expiré.'
            ], 401);
	    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
		    return response()->json([
                'success' => false,
                'error' => 'Le token transmis est invalide.'
            ], 401);
	    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
		    return response()->json([
                'success' => false,
                'error' => 'Le token est absent dans votre requête.'
            ], 401);
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
        return response()->json([
            $user, $data
        ]);
    }
}
