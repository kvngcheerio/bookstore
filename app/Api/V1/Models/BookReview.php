<?php

namespace App\Api\V1\Models;

use Yajra\Auditable\AuditableTrait;
use App\Api\V1\Models\Book;
use Illuminate\Database\Eloquent\Model;
use App\Api\V1\Traits\HandlesEventLogging;

class BookReview extends Model
{
    use AuditableTrait, HandlesEventLogging;
    
    public static function boot()
    {
    
    }

    protected $table = 'book_reviews';
    
    protected $fillable = ['book_id', 'title', 'review_text', 'rating', 'reply_text'];

    public function user()
    {
        return $this->belongsTo('App\Api\V1\Models\User', 'user_id');
    }
  
    public function book()
    {
        return $this->belongsTo('App\Api\V1\Models\Book', 'book_id');
    }

    //scopes
    public function scopeIsApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function addBookReview(array $review, int $goodId)
    {
        $data = [
            'user_id' => $this->reviewer(),
            'book_id' => $bookId
        ];
        //make sure their is no duplicate review by same person for same book
        return  $this->updateOrCreate($data, $review);
    
    }

    //get authenticated user or use 1 (admin)
    protected static function reviewer()
    {
        return auth()->check() ? auth()->id() : 1;
    }
}
