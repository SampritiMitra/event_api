<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class event_creator extends Model
{
    //
    public $create_rules=[
                'event_topic'=>['required'],
                'start_time'=>['required'],
                'end_time'=>['required'],
            ];
    public $accept_rules=[
                'status'=>['required'],
                // 'user_id' => Rule::requiredIf(User::where('id',auth()->id())->first('isAdmin')->isAdmin),
            ];
    public  $invite_rules=[
                'email'=>['required'],
            ];

    public $timestamps=false;
    protected $fillable = [
        'user_id', 'event_topic', 'start_time', 'end_time',
    ];
    public function creator()
    {
        return $this->belongsTo('App\User','user_id','id');
    }

    public function invitees()
    {
        return $this->hasMany('App\invite_status','event_id','id');
    }

    public static function create_e(Request $request){
        $event_creator=new event_creator;
        $validator=Validator::make($request->all(),$event_creator->create_rules);
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
        return [$invite_stat, $event];
    }

    public static function index_e(){
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

    public static function update_e(Request $request, $id){
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

    public static function destroy_event(Request $request, $id){
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
