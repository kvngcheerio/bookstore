<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Api\V1\Models\User;

class AdminUpdateUserPassword {
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $user;
	public $admin;

	/**
	 * Create a new event instance.
	 *
	 * @param User $admin
	 * @param User $user
	 */
	public function __construct(User $admin, User $user)
	{
		$this->user = $user;
		$this->admin = $admin;
	}
}
