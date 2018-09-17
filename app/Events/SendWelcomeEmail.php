<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Api\V1\Models\User;
use App\Mail\WelcomeMail;

class SendWelcomeEmail
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param $activation_link
     */
    public function __construct(User $user, $activation_link)
    {
        $this->user = $user;
        Mail::to($user->email)->queue(new WelcomeMail($user, $activation_link));
    }
}
