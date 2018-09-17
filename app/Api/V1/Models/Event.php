<?php

namespace App\Api\V1\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\SearchableByDateRange;

class Event extends Model
{
    use Filterable, SearchableByDateRange;

    protected $table = 'events';

    protected $fillable = ['event', 'event_enum_id', 'information', 'thread'];

    public function eventEnum()
    {
        return $this->belongsTo('App\Api\V1\Models\EventEnum', 'event_enum_id');
    }
}
