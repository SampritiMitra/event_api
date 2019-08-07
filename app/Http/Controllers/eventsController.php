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
use Illuminate\Validation\Rule;

class eventsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // constructor for authentication
    public function __construct()
    {
        $this->middleware('auth.basic.once');
    }


    // you may only see your events which you have created and events you are invited to unless you are admin
    public function index()
    {
        return event_creator::index_e();
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create(Request $request){
        [$invite_stat,$event]=event_creator::create_e($request);
        return response()->json([$event,$invite_stat],201);
    }

    

    public function accept(Request $request, $id)
    {
        //
        return invite_status::accept_user($request,$id);
    }



    public function invite(Request $request, $id)
    {
        //
        return invite_status::invite($request,$id);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
    //show own profile
    public function show(Request $request)
    {
        //
        if(User::where('id',auth()->id())->first('isAdmin')->isAdmin)
            return User::all();
        return User::where('id',auth()->id())->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    //show all members of events you are a part of including those you have created
    public function showMems()
    {
        //
        return invite_status::showMembers();
    }


    public function remove(Request $request, $id)
    {
        //
        return invite_status::remove($request,$id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        return event_creator::update_e($request,$id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        event_creator::destroy_event($request,$id);
    }
}
