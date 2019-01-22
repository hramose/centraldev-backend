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
        $user = DB::table('authentication')
                    ->join('developers', 'authentication.id', '=', 'developers.user_id')
                    ->select('authentication.email', 'developers.*')
                    ->where('developers.user_id', '=', $user->id)
                    ->get();

        return response()->json($user);
    }
}
