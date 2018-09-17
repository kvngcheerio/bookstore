<?php

namespace App\Http\Middleware;

use Closure;
use Dingo\Api\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Api\V1\Models\BookReview;

class UserCantReviewBookTwice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->book_id) {
            if ($bookReview = BookReview::where('book_id', $request->book_id)->first()) {
                if ($bookReview->user_id == BookReview::reviewer()) {
                    return new Response([
                        'error' => [
                            'message'=> 'you cannot review this item again',
                            'status_code' => 403
                            ]], 403);
                }
            }
        }

        return $next($request);
    }
}
