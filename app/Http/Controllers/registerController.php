<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Auth;
use App\event_creator;
use App\invite_status;
use App\User;
use Validator;

class registerController extends Controller
{
    //
    public function register(Request $request)
    {
        $user=User::register($request);
        return response()->json($user,201);
    }
}
