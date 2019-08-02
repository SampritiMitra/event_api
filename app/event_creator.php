<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class event_creator extends Model
{
    //
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
}
