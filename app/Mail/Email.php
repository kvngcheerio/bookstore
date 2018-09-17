<?php

namespace App\Mail;

use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Email extends Mailable
{
    use Queueable, SerializesModels;

    public $array;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $default_email = config('settings.default_email');
        $reply_to_address = config('settings.reply_to_address');
        $default_name = config('settings.default_name');

        return $this->view('mails.default')
                    ->with([
                        'email' => $this->array['email'],
                        'subject' => $this->array['subject'],
                        'body' => $this->array['body']
                    ])
                    ->from($default_email, $default_name)
                    ->replyTo($reply_to_address)
                    ->subject($this->array['subject']);
    }
}
