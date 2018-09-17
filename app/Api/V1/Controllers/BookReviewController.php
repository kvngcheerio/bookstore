<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Api\V1\Models\BookReview;
use APp\Api\V1\Models\Book;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('noreviewbooktwice', ['only' => ['store']]);
    }

    public function index()
    {
        $reviews = BookReview::with('creator')->get();
        if ($reviews->count()) {
            return $reviews;
        }
        
        return [];
    }
    /**
     * Persist a newly created review.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Api\V1\Models\BookReview $review
     * @param  \App\Api\V1\Models\Book $book
     *
     * @return \Dingo\Api\Http\Response
     */
    public function store(Request $request, BookReview $review, Book $book)
    {
        $this->validate($request, [
          'book_id' => 'required|integer',
          'title' => 'required',
          'rating' => 'required_without:review_text|integer',
          'review_text' => 'required_without:rating',
          'reply_text' => 'nullable'
           ]);
        //validation passed
        if (! $book = $book->find($request->book_id)) {
            throw new StoreResourceFailedException('reviewed item does not exist');
        }
        if ($review->addBookReview($request->except('token'), $request->book_id)) {
            return new Response(['status'=>  'review created'], 201);
        }
        throw new StoreResourceFailedException('review couldn\'t be stored');
    }

    /**
     * Display the specified review.
     *
     * @param App\Api\V1\Models\BookReview $review
     * @param  int $id
     *
     * @return  Dingo\Api\Http\Response
     */
    public function show(BookReview $review, $id)
    {
        if ($review = $review->with('creator')->find($id)) {
            return $review;
        }
        throw new NotFoundHttpException('review not found');
    }

    /**
    * Update a Review.
    *
    * @param \Illuminate\Http\Request   $request
    * @param \App\Api\V1\Models\BookReview   $review
    * @param int $bookId
    *
    * @return json
    */
    public function update(Request $request, BookReview $review, $bookId)
    {
        $this->validate($request, [
            'title' => 'required',
            'rating' => 'required_without:review_text|integer',
            'review_text' => 'required_without:rating',
            'reply_text' => 'nullable'
        ]);

        if (!$review = $review->find($id)) {
            throw new NotFoundHttpException('No review found');
        }

        if ($review->update($request->except('token'))) {
            return new Response(['status'=>  'review updated successfully'], 201);
        }
        throw new StoreResourceFailedException('review update failed');
    }

    /**
    * delete a review
    *
    * @param App\Api\V1\Models\BookReview $review
    * @param int $id
    *
    * @return  Dingo\Api\Http\Response
     */
    public function destroy(BookReview $review, $id)
    {
        if (! $review = $review->find($id)) {
            throw new DeleteResourceFailedException('review not found');
        }
        //delete review
        if ($review->delete()) {
            return new Response(['status'=>  'review deleted' ], 201);
        }
        //review not deleted
        throw new DeleteResourceFailedException('review delete request failed');
    }
}
