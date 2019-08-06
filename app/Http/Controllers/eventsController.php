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
         $admin=User::where('id',auth()->id())->first('isAdmin')->isAdmin;

        //Are you an admin?
        if($admin){
            $invitees=invite_status::all();
            $arr=array();
            if($invitees!==null){
                foreach($invitees as $invitee){
                    $A=$invitee->event;
                    $A['status']=$invitee->status;
                    array_push($arr,$A);
                }
            }
            return $arr;
        }

        //If not an admin  you may only see your events which you have created and events you are invited to
        $invitees=invite_status::where('user_id',auth()->id())->get();
        $arr=array();
        if($invitees!==null){
            foreach($invitees as $invitee){
                $A=$invitee->event;
                $A['status']=$invitee->status;
                array_push($arr,$A);
                // find events you are a part of, show their information and whther they are accepted, rejected or pending
            }
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

         //once you have created an event, you are also going to be added to the list of pending invitees

            //need the record of the latest event created by the current user so that we can set its status to pending in the invite_status table
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
                'status'=>['required',
                Rule::in(['Accepted', 'Pending','Rejected'])],
                'user_id' => Rule::requiredIf(User::where('id',auth()->id())->first('isAdmin')->isAdmin),
            ];
        $validator=Validator::make($request->all(),$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),400);
        }

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        $user;

        // Is the admin making changes?
        if(User::where('id',auth()->id())->first('isAdmin')->isAdmin){
            $user=$request['user_id'];
        }
        else{
            $user=auth()->id();
        }

         //Is the user invited to this event?
        if(invite_status::where('user_id',$user)->where('event_id',$id)->first()===null){
            return response()->json("The current user is not invited to this particular event",404);
        }
        
        //If okay, then update
        $event_stat=invite_status::where('user_id',$user)->where('event_id',$id)->update(['status'=>$request['status']]);

        //Send mail to required user
        $email=User::where('id',$user)->get();
        $body="You have changed your event status to";
        \Mail::to($email)->send(new \App\Mail\EventCreated($request['status'],$body));
        return response()->json($event_stat,200);
    }



    public function invite(Request $request, $id)
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
        // Or is the user an admin?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify && User::where('id',auth()->id())->first('isAdmin')->isAdmin==0)
            return response()->json("Unauthorized 401",401);

        $email=$request['email'];

        //Does the user being invited exist?
        if(User::where('email',$email)->first()===null)
            return response()->json("Invitee does not exist",401);

        //Okay,exists
        $uid=User::where('email',$email)->first()->id;

        //Is the user already a member or an invitee of this current event?
        if(invite_status::where('user_id',$uid)->where('event_id',$id)->first()!==null)
            return response()->json("This person has already received an invite",401);


        //Create record in invite_status table
        $invite_stat=invite_status::create([
                'user_id'=>$uid,
                'event_id'=>$id,
                'status'=>"Pending",
            ]);

        //Send email
        $body="You have been invited";
        \Mail::to($email)->send(new \App\Mail\EventCreated($request['email'],$body));
        return response()->json("Invitation sent",200);
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
        //Are you an admin?
        if(User::where('id',auth()->id())->first('isAdmin')->isAdmin){
            $events=event_creator::distinct()->get('id');
            $arr=array();
            foreach($events as $event){
                if(invite_status::where('event_id',$event->id)->where('status','Accepted')->first()!==null){
                    $mem=invite_status::where('event_id',$event->id)->where('status','Accepted')->get();
                    // return $mem;
                    $a=array();
                    foreach($mem as $m){
                        array_push($a,$m->creator->email);
                    }
                    $arr[$event->id]=$a;
                }
                else{
                    $a="No members yet";
                    $arr[$event->id]=$a;
                }
            }
            return $arr;
        }

        // if not admin then just show members of projects you have been invited to or are a part of
        $user_id=auth()->id();
        $events=invite_status::where('user_id',$user_id)->get();
        $arr=array();
        foreach($events as $event){
            if(invite_status::where('event_id',$event->event_id)->where('status','Accepted')->first()!==null){
                $mem=invite_status::where('event_id',$event->event_id)->where('status','Accepted')->get();
                $a=array();
                foreach($mem as $m){
                    array_push($a,$m->creator->email);
                }
                $arr[$event->event_id]=$a;
            }
            else{
                $a="No members yet";
                $arr[$event->event_id]=$a;
            }
        }
        return $arr;
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
        // Or is the user an admin?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify and !User::where('id',auth()->id())->first('isAdmin')->isAdmin)
            return response()->json("Unauthorized 401",401);

         //Does the user being deleted exist?
        $email=$request['email'];
        if(User::where('email',$email)->first()===null)
            return response()->json("Member does not exist",401);

        //get the user email who has to be removed
        $uid=User::where('email',$email)->first()->id;

        // Is the user a part of the event?
        if($event_stat=invite_status::where('user_id',$uid)->where('event_id',$id)->first()===null)
            return response()->json("That user cannot be removed since they are not a part of this event",404);

        $event_stat=invite_status::where('user_id',$uid)->where('event_id',$id)->delete();
        $body="You have been removed";
        \Mail::to($request['email'])->send(new \App\Mail\EventCreated($request['email'],$body));
        return response()->json(null,204);
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
        $rules=[
                'event_topic' => 'required_without_all:start_time,end_time',
                'start_time' => 'required_without_all:event_topic,end_time',
                'end_time' => 'required_without_all:event_topic,start_time',
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
        // or is the user an admin?
        $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify and !User::where('id',auth()->id())->first('isAdmin')->isAdmin)
            return response()->json("Unauthorized 401",401);

        $request['user_id']=auth()->id();
        event_creator::find($id)->update($request->all());

        //mail everyone who has been invited/is a member
        $members=invite_status::where('event_id',$id)->get();

        foreach($members as $member){
            $body="Event updation alert";
            $email=$member->creator()->first()->email;
            //send email to the user
            \Mail::to($email)->send(new \App\Mail\EventCreated($body,event_creator::find($id)));
        }
        return response()->json(event_creator::find($id),200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {

        //Does this event exist?
        if(event_creator::find($id)===null){
            return response()->json("Event does not exist",404);
        }

        //Does the current user create this event?
        // or is the user an admin? 
         $verify=event_creator::find($id)->user_id;
        if(auth()->id()!=$verify and !User::where('id',auth()->id())->first('isAdmin')->isAdmin)
            return response()->json("Unauthorized 401",401);

        //mail everyone who has been invited/is a member
        $members=invite_status::where('event_id',$id)->get();

        foreach($members as $member){
            $body="Event deletion alert";
            $email=$member->creator()->first()->email;
            \Mail::to($email)->send(new \App\Mail\EventCreated($body,event_creator::find($id)->event_topic));
        }

        event_creator::find($id)->delete();
        return response()->json("Event has been deleted",204);
    }
}
