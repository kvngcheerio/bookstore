<?php

namespace App\Listeners;

use App\Api\V1\Controllers\EventController;

class AuthEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function onUserLogin($event)
    {
        $this->describeUserEventAction($event, 'User logged in', 'User ', 'logged in');
        //create online status
        $event->user->setCache(5);
    }
    /**
     * Handle user logout events.
     */
    public function onUserLogout($event)
    {
        $this->describeUserEventAction($event, 'User logged out', 'User ', 'logged out');
        //destroy online status
        $event->user->pullCache();
    }

    /**
     * Handle user login events without activation.
     */
    public function onUserLoginNotActive($event)
    {
        $this->describeUserEventAction($event, 'Inactive User Tried Login', 'An inactive user ', 'tried to login');
    }
 
    /**
     * Handle failed login events.
     */
    public function onUserLoginFailed($event) {
        EventController::save( 'Non-User Login Failed', 4,
        'A non-user with IP address: ' . $event->ip . ' tried to login.', __FILE__
        );
    }
    /**
     * Handle user request password reset link events.
     */
    public function onUserRequestPasswordResetLink($event)
    {
        $this->describeUserEventAction($event, 'User requested password reset link', 'User ', 'requested for password reset link');
    }
    /**
     * Handle user reset password events.
     */
    public function onUserResetPassword($event)
    {
        $this->describeUserEventAction($event, 'User reset password', 'User ', 'has set a new password');
    }
    /**
     * Handle user account activation events.
     */
    public function onActivateUserAccount($event)
    {
        $this->describeUserEventAction($event, 'User account activated', '', 'activated their account');
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\Login',
            'App\Listeners\AuthEventSubscriber@onUserLogin'
        );

        $events->listen(
            'App\Events\LoginNotActive',
            'App\Listeners\AuthEventSubscriber@onUserLoginNotActive'
        );
        $events->listen(
            'App\Events\LoginFailed',
            'App\Listeners\AuthEventSubscriber@onUserLoginFailed'
        );

        $events->listen(
            'App\Events\Logout',
            'App\Listeners\AuthEventSubscriber@onUserLogout'
        );

        $events->listen(
            'App\Events\SendPasswordResetLinkEmail',
            'App\Listeners\AuthEventSubscriber@onUserRequestPasswordResetLink'
        );

        $events->listen(
            'App\Events\ResetPassword',
            'App\Listeners\AuthEventSubscriber@onUserResetPassword'
        );

        $events->listen(
            'App\Events\ActivateUserAccount',
            'App\Listeners\AuthEventSubscriber@onActivateUserAccount'
        );
    }

    /**
     * a helper to avoid repetition in the methods above
     *
     * @param  $event_object
     * @param  string $event_action
     * @param  string $event_start
     * @param  string $event_end
     */
    protected function describeUserEventAction($event_object, $event_action, $event_start, $event_end)
    {
        return EventController::save(
            $event_action,
            2,
            $event_start . $event_object->user->first_name . ' (' . $event_object->user->email . ') with ID: ' . $event_object->user->id . ' ' . $event_end, __FILE__
        );
    }
}
