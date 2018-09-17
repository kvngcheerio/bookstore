<?php namespace App\Api\V1\ModelFilters;

use EloquentFilter\ModelFilter;

class EventFilter extends ModelFilter
{
    /**
    * Related Models that have ModelFilters as well as the method on the ModelFilter
    * As [relationMethod => [input_key1, input_key2]].
    *
    * @var array
    */
    public $relations = [];


    /**
     * Filters for advanced search/query
     */
    public function search($search)
    {
        return $this->where(function ($query) use ($search) {
            return $query->where('name', 'LIKE', '%'.$search.'%')
                        ->orWhere('short_description', 'LIKE', '%'.$search.'%')
                        ->orwhereHas('categories', function ($new_query) use ($search) {
                            $new_query->where('name', 'LIKE', '%'.$search.'%')
                                    ->orWhere('description', 'LIKE', '%'.$search.'%')
                                    ->orWhere('meta_keyword', 'LIKE', '%'.$search.'%')
                                    ->orwhereHas('parent', function ($parent_query) use ($search) {
                                        $parent_query->where('name', 'LIKE', '%'.$search.'%')
                                                ->orWhere('description', 'LIKE', '%'.$search.'%')
                                                ->orWhere('meta_keyword', 'LIKE', '%'.$search.'%');
                                    })
                                    ->orwhereHas('children', function ($children_query) use ($search) {
                                        $children_query->where('name', 'LIKE', '%'.$search.'%')
                                                ->orWhere('description', 'LIKE', '%'.$search.'%')
                                                ->orWhere('meta_keyword', 'LIKE', '%'.$search.'%');
                                    });
                        });
                       
        });
    }
}
