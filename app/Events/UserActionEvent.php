<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Api\V1\Models\User;

class UserActionEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param int $action
     */
    public function __construct(User $user, int $action)
    {
        $this->user = $user;
        $this->action = $action;
    }
}
