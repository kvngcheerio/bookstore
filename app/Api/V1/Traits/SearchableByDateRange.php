<?php

namespace App\Api\V1\Traits;

use Illuminate\Database\Eloquent\Builder;

trait SearchableByDateRange
{

    /**
     * Searches the model by passed date ranges
     * 
     * @param Request $request
     * @param Builder $builder
     * @param string $from
     * @param string $to
     * 
     * @return querybuilder
     */
    public static function searchByDate($request, Builder $builder)
    {
        if ($request->has(['from', 'to'])) {
            $from = $request->input('from') . ' 00:00:00';
            $to = $request->input('to') . ' 23:59:59';

            return $builder->whereBetween('created_at', [$from, $to]);
        }
        if ($request->has('from')) {
            $from = $request->input('from');
            return $builder->where('created_at', '>=', $from);
        }
        if ($request->has('to')) {
            $to = $request->input('to');
            return $builder->where('created_at', '<=', $to);
        }

        return $builder;
    }


}
