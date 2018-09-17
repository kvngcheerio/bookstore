<?php

namespace App\Listeners;

use Carbon\Carbon;
use App\Api\V1\Controllers\EventController;
use App\Api\V1\Models\Event;

class UserActionsSubscriber
{

    /**
     * Handle user reset password events.
     */
    public function onAccountDetailsUpdated($event)
    {
        EventController::save('User account locked', 2, 'Account for user with ID: ' . $event->user->id . ' has been locked by user with ID: ' . $event->admin->id, __FILE__);
    }

    /**
     * Handle user delete account request.
     */
    public function onAccountDeletionRequested($event)
    {
        EventController::save('User requested that their account be deleted', 3, 'User with ID: ' . $event->user->id . ' requested that their account be deleted', __FILE__);
    }

    /**
     * Register the listeners for the admin actions subscriber.
     *
     * @param  Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\UserActionEvent',
            'App\Listeners\UserActionsSubscriber@onAccountDetailsUpdated'
        );

        $events->listen(
            'App\Events\AccountDeletionRequest',
            'App\Listeners\UserActionsSubscriber@onAccountDeletionRequested'
        );
    }


}
