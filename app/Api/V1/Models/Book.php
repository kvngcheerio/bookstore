<?php

namespace App\Api\V1\Models;

use Exception;
use EloquentFilter\Filterable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Yajra\Auditable\AuditableTrait;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Models\Picture;
use Illuminate\Database\Eloquent\Builder;
use App\Api\V1\Traits\HasPictures;
use App\Api\V1\Controllers\UserController;
use App\Api\V1\Traits\HandlesEventLogging;

class App extends Model
{
    use HasPictures, Filterable, HandlesEventLogging, AuditableTrait {
        
    }

    protected $table = 'books';
    
    protected $fillable = [
        'title', 'short_description', 'full_description', 'author_name',
        'price', 'pages_count'
    ];

    public static function boot()
    {
        parent::boot();
    
    

        static::created(function ($model) {
            //sku is done after creating cos that's the id ($model->getKey()) is assigned after a record is created
            $model->sku = $model->generateSku($model->name, $model->getKey(), 3);
            $model->save();
        });

       
    }
   
    public function reviews()
    {
        return $this->hasMany('App\Api\V1\Models\BookReview', 'book_id');
    }
    public function categories()
    {
        return $this->belongsToMany('App\Api\V1\Models\Category');
    }
    /**
     * Generate sku like xmp-0032
     */
    protected function generateSku($string, $id = null, $l = 2)
    {
        $results = ''; # empty string
        $vowels = ['a', 'e', 'i', 'o', 'u', 'y']; # vowels
        preg_match_all('/[A-Z][a-z]*/', ucfirst($string), $m); # Match every word that begins with a capital letter, ucfirst() is in case there is no uppercase letter
        foreach ($m[0] as $substring) {
            $substring = str_replace($vowels, '', strtolower($substring)); # String to lower case and remove all vowels
            $results .= preg_replace('/([a-z]{'.$l.'})(.*)/', '$1', $substring); # Extract the first N letters.
        }
        $results .= '-'. str_pad($id, 4, 0, STR_PAD_LEFT); # Add the ID
        return $results;
    }
}
