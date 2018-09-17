<?php

namespace App\Api\V1\Traits;

trait Authorizable
{

    private $abilities = [
        'index' => 'view',
        'edit' => 'edit',
        'show' => 'view',
        'update' => 'edit',
        'create' => 'add',
        'store' => 'add',
        'destroy' => 'delete',
        'getReaders' => 'view',
        'getInternalUsers' => 'view',
        'getUserByUsername' => 'view',
        'getUserByEmail' => 'view',
        'showAdminCreateUser' => 'view',
        'getUserDetailForAdmin' => 'view',
        'changePassword' => 'edit',
        'adminChangePassword' => 'edit',
        'showAuthenticatedUser' => 'view',
        ''

    ];

    /**
     * Override of callAction to perform the authorization before
     *
     * @param $method
     * @param $parameters
     * @return mixed
     */
    public function callAction($method, $parameters)
    {
        if ($ability = $this->getAbility($method)) {
            $this->authorize($ability);
        }

        return parent::callAction($method, $parameters);
    }

    public function getAbility($method)
    {
        //if the route has route name, get the Controller prefix from
        //the route name. eg users.index will get 'users'
        if ($routeName = \Request::route()->getName()) {
            $routeName = $this->routeNameByRoute($routeName);
        } else {
            //get the Controller prefix from the Controller name
            //explode it's snake_case
            //then pluralize. eg UserController becomes user_controller then 'users'
            $action = \Request::route()->getAction();
            $routeName = $this->routeNameByAction($action);
        }

        //check that the method eg 'store' has a value in our private $abilities array
        $action = array_get($this->getAbilities(), $method);

        //if it does return the value_$routeName  eg 'add_users'
        //$this->authorize($ability) from above, handles the rest
        //i.e it makes sure the user has this permission set in DB
        return $action ? $action . '_' . $routeName : null;
    }

    private function getAbilities()
    {
        return $this->abilities;
    }

    public function setAbilities($abilities)
    {
        $this->abilities = $abilities;
    }

    protected function routeNameByRoute(string $routeName): string
    {
        $routeName = explode('.', $routeName);
        return snake_case(camel_case($routeName[0]));
    }

    protected function routeNameByAction(array $action): string
    {
        $controller = class_basename($action['controller']);
        $array = explode('_controller@', snake_case($controller));
        return str_plural($array[0]);
    }
}
