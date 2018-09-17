<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Api\V1\Models\User;

class AdminUpdateJobTitle {
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $job_title;
	public $admin;
    public $action;

    /**
     * Create a new event instance.
     *
     * @param User $admin
     * @param $job_title
     * @param int $action
     */
	public function __construct(User $admin, $job_title, int $action)
	{
		$this->job_title = $job_title;
		$this->admin = $admin;
		$this->action = $action;
	}
}
