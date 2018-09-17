<?php

namespace App\Listeners;

use App\Helpers\Helper;
use App\Api\V1\Controllers\EventController;

class MailEventSubscriber
{
    /**
     * Handle email events.
     */
    public function onEmailSent($event)
    {
        $email = $event->email;
        EventController::save( 'Email sent', 2,
            'An email was sent to (' . $email['email'] . '). ' . 'Subject "' . $email['subject'] . '"', __FILE__
        );
    }

    public function onWelcomeEmailSent($event)
    {
        EventController::save( 'Welcome email sent', 2,
            'A welcome email was sent to ' . $event->user->first_name . ' (' . $event->user->email . ').', __FILE__
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            'App\Events\EmailSent',
            'App\Listeners\MailEventSubscriber@onEmailSent'
        );
        $events->listen(
            'App\Events\SendWelcomeEmail',
            'App\Listeners\MailEventSubscriber@onWelcomeEmailSent'
        );
    }
}
