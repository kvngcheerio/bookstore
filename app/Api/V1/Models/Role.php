<?php

namespace App\Api\V1\Models;

use Yajra\Auditable\AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\SpecialRoles;
use App\Api\V1\Traits\HandlesEventLogging;
use App\Api\V1\Traits\SearchableByDateRange;

class Role extends \Spatie\Permission\Models\Role
{
    use SpecialRoles, AuditableTrait, HandlesEventLogging, SearchableByDateRange;
}
