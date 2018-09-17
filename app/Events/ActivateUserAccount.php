<?php

namespace App\Events;

use App\Api\V1\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ActivateUserAccount
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;

	/**
	 * Create a new event instance.
	 *
	 * @param User $user
	 */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
