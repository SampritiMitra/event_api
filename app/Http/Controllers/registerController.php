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
                //'user_id'=>['required'],
                'name'=>['required'],
                'email'=>['required'],
                'password'=>['required'],
            ];

            $validator=Validator::make($request->all(),$rules);
            if($validator->fails()){
                return response()->json($validator->errors(),400);
            }
            
           //   $event=event_creator::create($request->all());
            $user=new User;
            $user->name=$request['name'];
            $user->email=$request['email'];
            $user->password=Hash::make($request['password']);
            $user->save();
     
            return response()->json($user,201);
         
    }
}
