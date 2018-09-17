<?php

namespace App\Exceptions;

use RuntimeException;

use App\Api\V1\Models\User;

class UserNotFoundException extends RuntimeException
{
    /**
     * Name of the affected Eloquent model.
     *
     * @var string
     */
    protected $user;

    /**
     * The affected model IDs.
     *
     * @var int|array
     */
    protected $ids;

    /**
     * Set the affected Eloquent model and instance ids.
     *
     * @param  string  $model
     * @param  int|array  $ids
     * @return $this
     */
    public function setUser($user, $ids = [])
    {
        $this->user = $user;
        $this->ids = array_wrap($ids);

        $this->message = "User not found [{$user}]";

        if (count($this->ids) > 0) {
            $this->message .= ' '.implode(', ', $this->ids);
        } else {
            $this->message .= '.';
        }

        return $this;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the affected Eloquent model IDs.
     *
     * @return int|array
     */
    public function getIds()
    {
        return $this->ids;
    }
}
