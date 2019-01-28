<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Authentication;
use App\Models\Developer;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function me()
    {
        $user = auth()->user();
        $user_id = $user->id;
        $user = DB::table('authentication')
                    ->join('developers', 'authentication.id', '=', 'developers.user_id')
                    ->select([
                        'authentication.email',
                        'developers.firstname',
                        'developers.lastname',
                        'developers.dob',
                        'developers.phone',
                    ])
                    ->where('developers.user_id', '=', $user_id)
                    ->first();
        $userAddress = DB::table('developers')
                            ->join('addresses', 'developers.user_id', '=', 'addresses.id')
                            ->select([
                                'addresses.*'
                            ])
                            ->where('developers.user_id', '=', $user_id)
                            ->first();

        $user->gravatar = 'https://www.gravatar.com/avatar/'.md5($user->email).'?s=512';
        $user->address = $userAddress;
        return response()->json([
            'profile' => $user
        ]);
    }
}
