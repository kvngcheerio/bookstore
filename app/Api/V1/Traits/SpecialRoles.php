<?php

namespace App\Api\V1\Traits;

trait SpecialRoles
{
    //array of special roles that can never be deleted.
    protected $specialRoles = ['super_admin', 'admin'];

    protected $canNotBeDeleted = ['super_admin', 'admin', 'internal_user', 'reader'];
        
    /**
     * function to check special roles
     */
    public function isSpecialRole()
    {
        return in_array($this->name, $this->specialRoles);
    }
    /**
     *  roles that cannot be deleted
     */
    public function canNotBeDeleted()
    {
        return in_array($this->name, $this->canNotBeDeleted);
    }
}
