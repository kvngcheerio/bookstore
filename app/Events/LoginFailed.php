<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class LoginFailed {
	use Dispatchable, InteractsWithSockets, SerializesModels;

	public $ip;

	/**
	 * Create a new event instance.
	 *
	 * @param $ip
	 */
	public function __construct( $ip ) {
		$this->ip = $ip;
	}
}
