<?php

namespace APp\Http\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Models\Role;
use App\Api\V1\Traits\Authorizable;

class RoleController extends Controller
{
    //use Authorizable;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Role  $role
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        //
    }
}
