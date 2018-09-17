<?php

namespace App\Listeners;

use Carbon\Carbon;
use App\Api\V1\Controllers\EventController;
use App\Api\V1\Models\Event;

class AdminActionsSubscriber {

    /**
     * Handle user reset password events.
     */
    public function onAdminUpdateUserDetail($event) {
        EventController::save('User account details updated', 2, 'Details for user with ID: ' . $event->user->id . ' was updated by user with ID: ' . $event->admin->id, __FILE__);
    }

    public function onAdminUpdateUserPassword($event) {
        EventController::save('User account password changed', 2, 'Account password for user with ID: ' . $event->user->id . ' was updated by user with ID: ' . $event->admin->id, __FILE__);
    }

    public function onAutoClearOldEvents($event) {

        EventController::clearOldEvents();

    }

    public function onAdminUpdateJobTitle($event) {
        switch ($event->action) {
            case -1://delete
                EventController::save('Job title updated', 2, 'Job title named: ' . $event->job_title->name . ' was deleted by user with ID: ' . $event->admin->id, __FILE__);
                break;
            case 0:
                EventController::save('Job title created', 2, 'Job title with ID: ' . $event->job_title->id . ' was created by user with ID: ' . $event->admin->id, __FILE__);
                break;
            case 1://update
                EventController::save('Job title deleted', 2, 'Job title with ID: ' . $event->job_title->id . ' was updated by user with ID: ' . $event->admin->id, __FILE__);
                break;
        }
    }

 
    /**
     * Register the listeners for the admin actions subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events) {
       

        $events->listen(
            'App\Events\AutoClearOldEvents',
            'App\Listeners\AdminActionsSubscriber@onAutoClearOldEvents'
        );

        $events->listen(
            'App\Events\AdminUpdateUserDetail',
            'App\Listeners\AdminActionsSubscriber@onAdminUpdateUserDetail'
        );

        $events->listen(
            'App\Events\AdminUpdateUserPassword',
            'App\Listeners\AdminActionsSubscriber@onAdminUpdateUserPassword'
        );

        $events->listen(
            'App\Events\AdminUpdateJobTitle',
            'App\Listeners\AdminActionsSubscriber@onAdminUpdateJobTitle'
        );

    }
}
