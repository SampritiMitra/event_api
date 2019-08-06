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
        //
        $rules=[
                'name'=>['required'],
                'email'=>['required','email'],
                'password'=>['required'],
            ];

            $validator=Validator::make($request->all(),$rules);
            if($validator->fails()){
                return response()->json($validator->errors(),400);
            }
            
            $arr=$request->all();
            $arr['password']=Hash::make($request['password']);
            $user=User::create($arr);
     
            return response()->json($user,201);
         
    }
}
