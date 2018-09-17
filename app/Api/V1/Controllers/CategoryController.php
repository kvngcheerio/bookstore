<?php

namespace App\Api\V1\Controllers;

use Carbon\Carbon;
use Tymon\JWTAuth\JWTAuth;
use Dingo\Api\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Api\V1\Models\User;
use App\Api\V1\Models\Picture;
use App\Api\V1\Models\Category;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use App\Api\V1\Requests\CreateCategoryRequest;
use App\Api\V1\Requests\UpdateCategoryRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $search = null;
        if ($request->input('search') != null) {
            $search = $request->input('search');
        }
        $categories = Category::query();
        if (!is_null($search)) {
            $categories = $categories->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('meta_keyword', 'like', '%' . $search . '%')
                    ->orWhere('meta_description', 'like', '%' . $search . '%')
                    ->with('parent');
            });
        }

        //date range
        $categories = Category::searchByDate($request, $categories);

        if ($items = $categories->get()) {
            return $items;
        }

        //for default symphony exceptions, pass empty array as 1st param,
        //then intended string message as 2nd param,
        return $this->response->array(['categories' => []]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request|CreateCategoryRequest $request
     * @param JWTAuth $auth
     *
     * @return \Dingo\Api\Http\Response
     */
    public function store(CreateCategoryRequest $request, JWTAuth $auth)
    {

        $user = UserController::getAuthUser($auth);

        if (!$user) {
            return $this->response->errorForbidden('Access denied!');
        }

        $data = array_filter(
            $request->only(
                'name',
                'description',
                'meta_keyword',
                'meta_description',
                'parent_id',
                'picture_id',
                'display_order',
                'created_by',
                'updated_by'
            ),
            'strlen'
        );

        $data['created_by'] = $user->id;

        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/books_category');
            $image->move($destinationPath, $name);

            $picture = Picture::create([
                'seo_filename' => $name,
                'mime_type' => $image->getClientOriginalExtension()
            ]);

            $data['picture_id'] = $picture->id;
        }

        $category = new Category($data);
        if ($category->save()) {
            return $this->response->created(null, ['status' => 'Ok', 'object' => $category]);
        } else {
            throw new StoreResourceFailedException();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Category
     */
    public function show($id)
    {
        if ($category = Category::where('id', $id)->with('parent')->first()) {
            return $category;
        }

        throw new NotFoundHttpException("Good category not found.");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request|UpdateCategoryRequest $request
     * @param JWTAuth $auth
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryRequest $request, JWTAuth $auth, $id)
    {
        $user = UserController::getAuthUser($auth);

        if (!$user) {
            return $this->response->errorForbidden('Access denied!');
        }

        $category = Category::where('id', $id)->first();

        if (!$category) {
            return $this->response->errorNotFound('The books category does not exist.');
        }

        if (Category::where('name', $request->name)->where('id', '!=', $id)->first()) {
            return $this->response->errorBadRequest('Books Category named \'' . $request->name . '\' already exist');
        }

        $data = array_filter($request->only(
            'name',
            'description',
            'meta_keyword',
            'meta_description',
            'parent_id',
            'display_order'
        ), 'strlen');

        $data['updated_by'] = $user->id;

        $picture_id = $category->picture_id == null ? 0 : $category->picture_id;

        if ($request->hasFile('picture')) {
            $image = $request->file('picture');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/images/books_category');
            $image->move($destinationPath, $name);

            $picture = Picture::updateOrCreate(['id' => $picture_id], [
                'seo_filename' => $name,
                'mime_type' => $image->getClientOriginalExtension()
            ]);

            $data['picture_id'] = $picture->id;
        }

        if ($category->update($data)) {
            return $this->response->accepted(null, ['status' => 'Goods category updated']);
        } else {
            throw new UpdateResourceFailedException();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Category::where('id', $id)->first()->delete()) {
            return $this->response->accepted(null, ['status' => 'Books category deleted.']);
        }

        throw new DeleteResourceFailedException('Error encountered, unable to delete goods category at the moment.');
    }

    public function deleteMulti(Request $request)
    {
        if ($request->has('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                $item = (int)$item;
                Category::where('id', $item)->delete();
            }
            return $this->response->accepted('Books categories deleted.');
        }
        return $this->response->errorBadRequest("No items to delete");
    }
}
