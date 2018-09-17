<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Database\Eloquent\Collection;
use App\Api\V1\Controllers\UserController;
use Exception;

class HideSensitiveInformation {

    protected $itemsToRemove = [
        'created_by', 
        'creator', 
        'updated_by', 
        'updater', 
        'created_at',
        'updated_at'
        ];

    public function handle($request, Closure $next)
    {
        $response = $next($request);
        //check if the user is auth     
        try {            
            $user = JWTAuth::parseToken()->authenticate();
        } catch(Exception $e) { 
               
        }   

        return $response;
    }
    
    /**
     * remove keys from response
     */
    protected function removeFromReponse($response, $request) {
        $keys = $this->getRemoveable();

        if ($original = $response->getOriginalContent()) {
            if ($original instanceof Collection) {
               // $original = $original->toArray();
               $this->recursiveUnsetCollection($original, $keys);
            }
            if (is_array($original)) {
                $this->recursiveUnsetArray($original, $keys);
            }

            //replace the modified content    
            try {            
                $user = JWTAuth::parseToken()->authenticate();
                if ( ($request->segment(2) == 'refresh') || !$user) {
                    $response->setContent(collect($original));
                } else {
                    $response->setContent($original);                
                }
            } catch(Exception $e) { 
                $response->setContent(collect($original));                                
            }         
            
        }

        return;
    }
    /**
     * get items/keys to remove from response
     */
    protected function getRemoveable() {
        return $this->itemsToRemove;
    }
    /**
     * unset keys from arrays inside a collection
     */
    protected function recursiveUnsetCollection(Collection $collection, $unwanted_keys) {
        $collection->each(function($coll) use ($unwanted_keys) {
            //dd($coll);
            $this->recursiveUnsetArray($coll, $unwanted_keys);
        });
    }
    /**
     * unset keys from an array
     */
    protected function recursiveUnsetArray(&$array, array $unwanted_keys) {
        foreach($unwanted_keys as $unwanted_key) {
            $this->_recursiveUnsetArray($array, $unwanted_key);
        }
        return;
    }
    /**
     * unset a key from an array
     */
    protected function _recursiveUnsetArray(&$array, $unwanted_key) {
        unset($array[$unwanted_key]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                $this->_recursiveUnsetArray($value, $unwanted_key);
            }
        }
    }
}