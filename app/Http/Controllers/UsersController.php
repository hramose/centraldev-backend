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
        $data = ['is_company' => true, 'company_size' => 1, 'company_role' => 'ceo'];
        return response()->json([
            $user, $data
        ]);
    }
}
