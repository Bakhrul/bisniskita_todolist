<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;

class UserController extends Controller
{
      public function user()
    {
        $user = DB::table('m_users')
            ->where('us_id', Auth::user()->us_id)
            ->first();

        return response()->json($user);
    }
}
