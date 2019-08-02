<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $project;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($project)
    {
        //
        $this->project=$project;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('mail.event-created');
    }
}
