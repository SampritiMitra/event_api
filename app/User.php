<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $timestamps=false;
    protected $fillable = [
        'name', 'email', 'password', 'isAdmin',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function register(Request $request){
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
            return $user;
    }
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany('App\event_creator','user_id','id');
    }

    public function invitees()
    {
        return $this->hasMany('App\invite_status','user_id','id');
    }

}
