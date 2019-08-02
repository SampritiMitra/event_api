<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invite_status extends Model
{
    //
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
}
