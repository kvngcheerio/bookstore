<?php

namespace App\Api\V1\Controllers;

use Exception;
use ZipArchive;
use finfo;
use Dingo\Api\Http\Response;
use Maatwebsite\Excel\Excel;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Models\Book;
use App\Api\V1\Models\User;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Config;
use App\Api\V1\Models\Picture;
use Illuminate\Support\Facades\Storage;
use App\Api\V1\Models\Category;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Api\V1\Controllers\UserController;
use App\Api\V1\Requests\CreateBookRequest;
use App\Api\V1\Requests\UpdateBookRequest;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Illuminate\Support\Facades\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookController extends Controller
{
    protected $tempDirName = 'tmp';

    /**
     * get all books
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Api\V1\Models\Book  $book
     *
     * @return \Dingo\Api\Http\Response
     */
    public function index(Request $request)
    {
        $books = Book::with('pictures')
                    ->with('categories')
                    ->with('creator')
                    ->paginate();
        return new Response(compact('books'), 200);
    }
     /**
     * search all books
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Api\V1\Models\Book  $book
     *
     * @return \Dingo\Api\Http\Response
     */

    /**
     * Persist a newly created book.
     *
     * @param  \App\Api\V1\Requests\CreateBookRequest  $request
     * @param  \App\Api\V1\Models\Book $book
     *
     * @return \Dingo\Api\Http\Response
     */
    public function store(CreateBookRequest $request, Book $book)
    {
        
        //validation passed
        if ($book = $book->create($request->except(['token', 'picture']))) {
            //add categories
            $categories = Category::findOrFail($request->category);
            $book->categories()->sync($categories, false);

            if ($request->hasFile('picture')) {
                //get picture file(s) from request
                $picture = Picture::getPicture($request);

                if (! $book->attachPictures($picture, $request->name, 'books')) {
                    throw new StoreResourceFailedException('couldn\'t store book\'s pictures');
                }
            }
            return $book;
        }
        throw new StoreResourceFailedException('book couldn\'t be stored');
    }
    

    /**
     * Display the specified number of latest books.
     *
     * @param  int $id
     *
     * @return  \Dingo\Api\Http\Response
     */
    public function latest($amount = 5)
    {
        $book = Book::with('pictures')
                    ->with('categories')
                    ->with('creator')
                    ->orderBy("id", "desc")
                    ->take($amount)
                    ->get();
      
        return $book->count() ? $book : [];
    }
    /**
     * Display the specified book.
     *
     * @param  int $id
     *
     * @return  \Dingo\Api\Http\Response
     */
    public function show($id)
    {
        if ($book = Book::with('pictures')
                ->with('categories')
                ->with('creator')
                ->find($id)) {
            return $book;
        }
        throw new NotFoundHttpException('book not found');
    }

    /**
    * Update a book.
    *
    * @param \Illuminate\Http\Request   $request
    * @param \App\Api\V1\Models\Book   $book
    * @param int $id
    *
    * @return  \Dingo\Api\Http\Response
    */
    public function update(UpdateBookRequest $request, Book $book, $id)
    {
        if (!$book = $book->find($id)) {
            throw new NotFoundHttpException('book not found');
        }

        if ($book->update($request->except(['token', 'picture']))) {
            //update categories
            $categories = Category::findOrFail($request->category);
            $book->categories()->sync($categories, false);

            
            return new Response(['status'=>  'book updated successfully'], 201);
        }
        throw new StoreResourceFailedException('Books update failed');
    }

    /**
    * delete a book
    *
    * @param App\Api\V1\Models\Book $book
    * @param int $id
    *
    * @return  Dingo\Api\Http\Response
     */
    public function destroy(Book $book, $id)
    {
        if (! $book = $book->find($id)) {
            throw new DeleteResourceFailedException('booknot found');
        }
        //delete book
        if ($book->delete()) {
              //remove categories relationship                
              $book->categories()->detach(); 
              
              //if book has pictures, delete them
              if ($pictures = $book->hasPictureInDb()) {
                if (!$book->detachPictures($pictures)) {
                      throw new DeleteResourceFailedException('book images could not be deleted');
                  }
              }

            return new Response(['status'=>  'book deleted' ], 201);
        }
        //book not deleted
        throw new DeleteResourceFailedException('book delete request failed');
    }

    /**
    * store a book's picture
    *
    * @param Illuminate\Http\Request $request
    *
    * @return Response
    */
    public function addPicture(Request $request)
    {
        $this->validate($request, [
        'book_id' => 'required',
        'picture' => 'required|image|max:2048'
         ]);
        $book = Book::findOrFail($request->book_id);
        //make sure book has at most 5 pictures
        $picture = Picture::getPicture($request);
        
        if ($pictures = $gbook->attachPictures($picture, $book->title, 'books')) {
            return new Response($pictures, 201);
        }
        throw new StoreResourceFailedException('couldn\'t upload picture');
    }
    /**
    * delete a book's picture
    *
    * @param Illuminate\Http\Request $request
    * @param App\Api\V1\Models\Picture $picture
    *
    * @return Response
    */
   
    /**
     * mass import books from an excel|csv file
     *
     * @param Illuminate\Http\Request $request
     * @param Maatwebsite\Excel\Excel $excel
     *
     * @return Response
     */
    public function massImport(Request $request, Excel $excel)
    {
        $rules = [
        'file' => 'required|max:2048|mimetypes:application/vnd.ms-excel,text/plain,text/csv,text/tsv,application/excel,
                        application/vnd.ms-excel,application/vnd.msexcel',
        'pictures' => 'file|max:32768'
        ];
        $this->validate($request, $rules, ['file.mimetypes' => 'only Excel and CSV files are allowed']);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->getRealPath();
            $data = $excel->load($path)->get();
            
            if (!empty($data) && $data->count()) {
                $zip = false;            
                
                //extract pictures, if there's zip of pictures
                if ($request->hasFile('pictures')) {
                    //get pictures zip path
                    $zipPath = $request->file('pictures')->getRealPath();            
                    $zip = new ZipArchive();
                    //attempt opening the file
                    if( $zip->open($zipPath) === TRUE )
                    {
                        $tempDir = $this->getTempDir();
                        Storage::makeDirectory($tempDir, 0711, true, true); 
                        $zip->extractTo($tempDir);
                        $tempPictures = Storage::disk('local')->allFiles($tempDir);
                        $tempPictures = array_values(array_diff(scandir($tempDir), array('.', '..', '__MACOSX')));
                        //$zip->close();   
                    } else {
                        $zip = false;                        
                    }
                }

                $count = 0;
                foreach ($data->toArray() as $row) {
                    if (!empty($row)) {
                        $message = ($count > 0)? ' Some of your data have been added. Review your books and delete from your import file, to avoid duplicates':'';
                        //remove empty array fields
                        $row = array_filter(array_map('trim', $row), 'strlen');
                        $rules = Config::get('apiauth.create_book.validation_rules');
                        //validate each row
                        $validator = Validator::make($row, $rules);
                        if ($validator->fails()) {
                            if ($count > 0) {
                                continue;                                 
                            } else {
                                 return new Response([
                                    'error' => [
                                    'message'=> 'validation failed',
                                    'status_code' => 401,
                                    'errors' => $validator->errors()
                                    ]], 401);
                            } 
                        }
                        //validate categories
                        if (!$categories = $this->validateCategory($row['category'])) {
                            //if this is the first iteration, just stop
                            if ($count > 0) {
                                continue;                           
                            } else {
                                throw new StoreResourceFailedException('some provided categories are invalid. format document correctly and try again.'.$message);
                            }
                        }
                      
                        $_row = array_except($row, ['category']);
                        //create book
                        if (!$book = Book::create($_row)) {
                            if ($count > 0) {
                                continue;                           
                            } else {
                                throw new StoreResourceFailedException('an error occured, please try again'.$message);
                            }
                        }
                        //add categories
                        $book->categories()->sync($categories, false);

                        //grab pictures
                        if($zip && array_key_exists('pictures', $row)) {
                            $picturesArray = $this->getPicturesFromString($row['pictures']);
                            $this->attachPictureArrayBooks($tempPictures, $picturesArray, $book);
                        }                       
                        $count++;
                    }
                }
                $total = count($data->toArray());
                $failed = $this->getUploadFailedMessage($total, $count);
                if ($zip) {
                    $zip->close();   
                    //delete picture tmp folder
                    Storage::deleteDirectory($tempDir);      
                }                              
                
                return new Response([
                    'status' =>  'batch import completed.',
                    'successful' =>  "$count",
                    'failed' =>  "$failed",
                ], 201);
            }
        }

        throw new StoreResourceFailedException('import failed. please check your file and try again');
    }
    
    /**
     * download sample import books excel/csv file
     */
    public function sampleImportBooks($fileType)
    {
        switch ($fileType) {
            case 'csv':
            $ext = 'csv';
                break;
            case 'excel':
            $ext = 'xls';
                break;            
            default:
            $ext = null;
                break;
        }
        if (is_null($ext)) {
            throw new NotFoundHttpException('file not found');    
        }
        $fileUrl = public_path() .'/storage/import_books.'. $ext;
        return HttpResponse::download($fileUrl);
    }

  

    /**
     * validate that given categories (comma seperated) are in db
     */
    protected function validateCategory(string $categoriesString)
    {
        $categories = $this->getCategoriesFromString($categoriesString);
        if ($category_ids = (new Category)->areValidCategories($categories)) {
            return $category_ids;
        }
        return false;
    }
  
    /**
     * get categories from a comma seperated string
     */
    protected function getCategoriesFromString(string $string)
    {
        $array = explode(',', $string);
        return array_map('trim', $array);
    }
    /**
     * set failed message for batch books upload
     */
    protected function getUploadFailedMessage($total, $success) {
        $failed = $total - $success;
        if ($failed > 0) {
            if ($success > 0) {
                return $failed . '. However, some books were successfully added. inspect your books, remove the successful ones from the uploaded document to avoid duplicates. Then make adequate corrections to the document and re-upload.';
            }
            return $failed . '. Please check the FAQ page for information about batch books upload.';
        } else {
            return 0;
        }
    }
    /**
     * get pictures from a comma seperated string
     */
    protected function getPicturesFromString(string $string)
    {
        //we use method from categories as they both do same things
        return $this->getCategoriesFromString($string);
    }   
    /**
     * upload pictures from zip and link to books through the names in array
     * 
     * @param array $tempPictures
     * @param array $picturesArray
     * @param Book $book
     */
    protected function attachPictureArrayBooks($tempPictures, array $picturesArray, $book)  {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        
        foreach ($picturesArray as $picture) {
            try {
                $filePath = $this->getTempFile($picture);
                if (in_array($picture, $tempPictures)) {
                    $image = new UploadedFile( 
                        $filePath, 
                        $picture, $finfo->file($filePath),
                        null,null,true);
                        //attach picture to book
                        $book->attachPictures($image, $book->title, 'books');
                }
            } catch (Exception $e) {
                //do nothing
            }            
        }
    }
    /**
     * get temporary upload directory
     */
    protected function getTempDir() {
        return storage_path() . '/' . $this->tempDirName . '/' . auth()->user()->username . '/';
    }
    /**
     * make a temporary uploaded file path
     */
    protected function getTempFile(string $file) {
        return $this->getTempDir() . $file;
    }
}
