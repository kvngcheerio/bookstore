<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Api\V1\Models\User;
use Illuminate\Queue\SerializesModels;
use Wpb\String_Blade_Compiler\Facades\StringBlade;


class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    //public properties are automtically made available in the view
    public $activation_link;


    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param $activation_token
     */
    public function __construct(User $user, $activation_link)
    {
        $this->user = $user;
        $this->activation_link = $activation_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = config('message_template.activation_email.subject');
        $template = config('message_template.activation_email.template');
        $default_email = config('settings.default_email');
        $default_name = config('settings.default_name');
        $reply_to_address = config('settings.reply_to_address');
        
        
        return $this->view([ 'html' => ['template' => $template] ],
                [
                    'first_name' => $this->user->first_name,
                ]
            )->from($default_email, $default_name )
            ->replyTo($reply_to_address)
            ->subject($subject);
    }
}
