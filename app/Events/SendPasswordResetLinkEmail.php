<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Api\V1\Models\User;
use App\Mail\RequestPasswordResetLinkMail;

class SendPasswordResetLinkEmail
{

    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $reset_link;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $reset_link)
    {
        $this->user = $user;
        $this->reset_link = $reset_link;
        Mail::to( $user->email )->queue( new RequestPasswordResetLinkMail( $user, $reset_link ) );
    }
}
