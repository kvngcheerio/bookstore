<?php

namespace App\Api\V1\Models;

use Yajra\Auditable\AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\HandlesEventLogging;

class Setting extends Model
{
    use AuditableTrait, HandlesEventLogging;
    
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];
}
