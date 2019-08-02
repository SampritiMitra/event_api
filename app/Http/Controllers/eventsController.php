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

    public function __construct()
    {
        $this->middleware('auth.basic.once');
    }

    public function index()
    {
        // you may only see your events
        //events you have created and events you are invited to
        $events=invite_status::where('user_id',auth()->id())->get('id');
        $arr=array();
        foreach($events as $event){
            array_push($arr,invite_status::find($event->id)->event,
                invite_status::where('id',$event->id)->get('status')[0]);
        }
        return $arr;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function create(Request $request){

          // validate and create the event
    		$rules=[
                'event_topic'=>['required'],
                'start_time'=>['required'],
                'end_time'=>['required'],
    		];

    		$validator=Validator::make($request->all(),$rules);
    		if($validator->fails()){
    			return response()->json($validator->errors(),400);
    		}
    		
            $arr=$request->all();
            $arr['user_id']=auth()->id();
           	$event=event_creator::create($arr);

     //    //once you have created an event, you are also going to be added
            // to the list of pending invitees

            //need the record of the latest event created by the current user
            //so that we can set its status to pending in the invite_status table
            $e_id=DB::table('event_creators')->where('user_id', auth()->id())->orderBy('id', 'desc')->first()->id;

            $invite_stat=invite_status::create([
                'user_id'=>auth()->id(),
                'event_id'=>$e_id,
                'status'=>"Pending",
            ]);

            return response()->json([$event,$invite_stat],201);
    }

    

    public function accept(Request $request, $id)
    {
        //
        $rules=[
                //'user_id'=>['required'],
                'status'=>['required',
                Rule::in(['Accepted', 'Pending','Rejected'])],
            ];
        $validator=Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        //Did the current user create this event?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify)
            return response()->json("Unauthorized 401",401);

        $event_stat=invite_status::where('user_id',auth()->id())->where('event_id',$id)->update(['status'=>$request['status']]);
        return response()->json($event_stat,200);
    }

    public function invite(Request $request, $id)
    {
        //
        $rules=[
                //'user_id'=>['required'],
                'email'=>['required'],
            ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        //Did the current user create this event?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify)
            return response()->json("Unauthorized 401",401);

        $email=$request['email'];
        $uid=User::where('email',$email)->get()[0]->id;
        //return $uid;
        $invite_stat=invite_status::create([
                'user_id'=>$uid,
                'event_id'=>$id,
                'status'=>"Pending",
            ]);

        return response()->json("Invitation sent",200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        //
        return User::where('id',auth()->id());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }


    public function remove(Request $request, $id)
    {
        //
        $rules=[
                'email'=>['required'],
            ];

        $validator=Validator::make($request->all(),$rules);
        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        //Did the current user create this event?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify)
            return response()->json("Unauthorized 401",401);

        //get the user email who has to be removed
        $uid=User::where('email',$request['email'])->get()[0]->id;
        $event_stat=invite_status::where('user_id',$uid)->where('event_id',$id)->delete();
        return response()->json(null,204);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, event_creator $event_creator)
    {
        //
        $rules=[
                'event_topic'=>['required'],
             //   'start_time'=>['required'],
            //    'end_time'=>['required'],
    		];

		$validator=Validator::make($request->all(),$rules);
		if($validator->fails()){
			return response()->json($validator->errors(),400);
		}

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        //Did the current user create this event?
        $verify=$event_creator->user_id;
        if(auth()->id()!=$verify)
            return response()->json("Unauthorized 401",401);

        $request['user_id']=auth()->id();
        $event_creator->update($request->all());
        return response()->json($event_creator,200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, event_creator $event_creator)
    {
        //Does the current user create this event?
        $verify=$event_creator->user_id;
        if(auth()->id()!=$verify)
            return response()->json("Unauthorized 401",401);

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        $event_creator->delete();
        return response()->json(null,204);

    }
}
