<?php namespace App\Api\V1\ModelFilters;

use EloquentFilter\ModelFilter;

class BookFilter extends ModelFilter
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
        $search = urldecode($search);
        return $this->where(function ($query) use ($search) {
            return $query->where('title', 'LIKE', '%' . $search . '%')
                ->orWhere('short_description', 'LIKE', '%' . $search . '%')
                ->orwhereHas('categories', function ($new_query) use ($search) {
                    $new_query->where('name', 'LIKE', '%' . $search . '%')
                        ->orWhere('description', 'LIKE', '%' . $search . '%')
                        ->orWhere('meta_keyword', 'LIKE', '%' . $search . '%')
                        ->orwhereHas('parent', function ($parent_query) use ($search) {
                            $parent_query->where('name', 'LIKE', '%' . $search . '%')
                                ->orWhere('description', 'LIKE', '%' . $search . '%')
                                ->orWhere('meta_keyword', 'LIKE', '%' . $search . '%');
                        })
                        ->orwhereHas('children', function ($children_query) use ($search) {
                            $children_query->where('name', 'LIKE', '%' . $search . '%')
                                ->orWhere('description', 'LIKE', '%' . $search . '%')
                                ->orWhere('meta_keyword', 'LIKE', '%' . $search . '%');
                        });
                });
        });
    }
    public function minDate($min_date)
    {
        return $this->where(function ($query) use ($min_date) {
            return $query->where('created_at', '>=', $min_date);
        });
    }
    public function maxDate($max_date)
    {
        return $this->where(function ($query) use ($max_date) {
            return $query->where('created_at', '<=', $max_date);
        });
    }
    public function minPrice($min_price)
    {
        return $this->where(function ($query) use ($min_price) {
            return $query->where('price', '>=', $min_price);
        });
    }
    public function maxPrice($max_price)
    {
        return $this->where(function ($query) use ($max_price) {
            return $query->where('price', '<=', $max_price);
        });
    }
   

    //relationships
    public function category($category)
    {
        $category = urldecode($category);
        return $this->related('categories', function ($query) use ($category) {
            return $query->where('name', $category);
        });
    }
   
}
