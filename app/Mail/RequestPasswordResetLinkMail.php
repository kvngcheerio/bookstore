<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Api\V1\Models\User;

class RequestPasswordResetLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reset_link;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, $reset_link)
    {
        $this->user = $user;
        $this->reset_link = $reset_link;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = config('message_template.password_reset_email.subject');
        $template = config('message_template.password_reset_email.template');
        $default_email = config('settings.default_email');
        $default_name = config('settings.default_name');
        $reply_to_address = config('settings.reply_to_address');

        return $this->view([ 'html' => ['template' => $template] ],
                        [
                            'first_name' => $this->user->first_name,
                        ]
                    )->from($default_email, $default_name)
                    ->replyTo($reply_to_address)
                    ->subject($subject);
    }
}
