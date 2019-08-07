<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
// use Auth;
// use App\event_creator;
// use App\invite_status;
// use App\User;
use Validator;
// use Illuminate\Validation\Rule;

class invite_status extends Model
{
    //
    public $rules=[
                'email'=>['required'],
            ];

    public $timestamps=false;
    protected $fillable = [
        'user_id', 'event_id', 'status', 
    ];


    public function event()
    {
        return $this->belongsTo('App\event_creator','event_id','id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public static function accept_user(request $request, $id){
        $event_creator=new event_creator;
        $validator=Validator::make($request->all(),$event_creator->accept_rules);
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

    public static function invite(Request $request, $id){
         $event_creator=new event_creator;
        $validator=Validator::make($request->all(),$event_creator->invite_rules);
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

    public static function showMembers(){
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

    public static function remove(Request $request, $id){
        $invite_status=new invite_status;
        $validator=Validator::make($request->all(),$invite_status->rules);
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
}
