<?php

namespace App\Api\V1\Models;

//use Illuminate\Http\File;
use Exception;
use Illuminate\Http\UploadedFile;
use Tymon\JWTAuth\Facades\JWTAuth;
use Yajra\Auditable\AuditableTrait;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Dingo\Api\Http\FormRequest as Request;

class Picture extends Model
{
    use AuditableTrait;

    protected $table = 'pictures';
   
    protected $fillable = ['path', 'mime_type', 'seo_filename', 'is_new'];
    
    protected $hidden = ['pivot'];

    protected $pictureExpiryDate = '';

    public static function boot()
    {
        parent::boot();    
        /**
         * scope to return only pictures that the user owns
         */
        static::addGlobalScope('isOwner', function (Builder $builder) {
            try {
                $user = JWTAuth::parseToken()->authenticate(); 
                if ($user->hasPermissionTo('view_pictures')) {
                    return $builder;                
                }
                if ($user) {
                    return $builder->where('created_by', $user->id);                
                }             
            } catch (Exception $e) {
                return $builder->where('created_by', -1000);                        
            }
            //-1000 is just a random negative number to make sure the builder returns nothing
            return $builder->where('created_by', -1000);            
        });
    }

    /**
     * save picture to dir and store in db
     *
     * @param Illuminate\Http\UploadedFile | array $picture
     * @param string $pictureName
     * @param string $picture
     */
    public static function savePicture($picture, string $pictureName = null, string $dir = null)
    {
        $self = new self();
        
        if (is_array($picture)) {
            //array of pictures
            $pictures = [];
            foreach ($picture as $p) {
                if (!$saved = $self->_savePicture($p, $pictureName, $dir)) {
                    //break if there's an error
                    return $saved;
                }
                $pictures[] = $saved;
            }
            //array of created objects
            return $pictures;
        } else {
            //single picture
            return $self->_savePicture($picture, $pictureName, $dir);
        }
    }
    /**
    * delete picture. if picture object is passed, delete those pictures
    *
    * @param $this $picture
    *
    * @return $this | array
    */
    public static function deletePicture($picture)
    {
        $self = new self(); 

        $path = $picture->pluck('path');

        $deletedPictures = [];
        foreach ($path as $p) {
            if (! $deleted = $self->_deletePicture($p)) {
                return $deleted;
            }
            $deletedPictures[] = $deleted;
        }
        //array of deleted picture objects
        return $deletedPictures;
    }

    /**
     * get picture file from request
     *
     * @param Dingo\Api\Http\FormRequest | Illuminate\Http\Request $request
     */
    public static function getPicture($request)
    {
        $picture = $request->file('picture');
        if (is_array($picture)) {
            $pictures = [];
            foreach ($picture as $p) {
                $pictures[] = $p;
            }
            return $pictures;
        }
        return $picture;
    }
    /**
     * save picture to disk
     *
     * @param UploadedFile $picture
     * @param string $pictureName
     * @param string $dir
     *
     * @return bool|Picture
     */
    protected function _savePicture(UploadedFile $picture, string $pictureName = null, string $dir = null)
    {
        //save picture to disk /time()/
        if ($path = $picture->store($this->getPictureDirectory($dir))) {
            //intervention resize picture
            $img = Image::make(storage_path().'/app/' . $path);
            $img = $img->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            //intervention save picture. without path, intevention will override the original picture, which is what we want.
            $img->save();

            $data = [
                'path' => $path,
                'mime_type' => $picture->getClientMimeType(),
                'seo_filename' => $this->getPictureName($picture, $pictureName)
            ];
            //persist to db
            return $this->create($data);
        }

        return false;
    }
    /**
    * delete picture by the path
    *
    * @param string $path
    *
    * @return bool|$this
    */
    protected function _deletePicture(string $path)
    {
        $deleted = false;

        if (Storage::delete($path)) {
            //picture deleted
            //delete from db
            try {
                //$deleted is the deleted picture object
                $delete = $this->where('path', $path)->first();
                $deleted = $delete;
                $delete->delete();
            } catch (Exception $e) {
                $deleted = false;
            }
        }
        
        return $deleted;
    }
    /**
     * get picture name
     *
     * @param UploadedFile $picture
     * @param string $pictureName
     *
     * @return string
     */
    protected function getPictureName(UploadedFile $picture, string $pictureName = null)
    {
        return is_null($pictureName)? $picture->getClientOriginalName() : $pictureName;
    }
    /**
     * get directory to store picture
     *
     * @param string $dir
     *
     * @return string
     */
    protected function getPictureDirectory(string $dir = null)
    {
        //directory is of the form 2017/10
        //if a directory is passed, also prepend the directory
        $default =  date('Y') . '/' . date('m');
        $dir = is_null($dir) ? 'public/' . $default : 'public/' . $dir . '/' . $default;

        return $dir;
    }
    /**
     * get old pictures in the db
     * 
     * @param null
     * @return mixed
     */
    public static function getOldPictures() {
        $self = new self;
        $expiryDate = $self->getExpiryDate();
        $_oldPictures = $self->where('created_at', '>=', $expiryDate)->get();
        if ($_oldPictures->count()) {
            return $_oldPictures;
        }

        return false;
    }
    public static function isOrphaned(self $picture) {
        $self = new self;
        if ($oldPictures = self::getOldPictures()) {
            
        }

        return false;
        //array_diff
        //return $permissions === array_intersect($permissions, $this->pluck('name')->toArray());  
    }
    /**
     * get old date
     * 
     * @param null
     * @return string
     */
    protected function getExpiryDate() {
        return $this->pictureExpiryDate;
    }
}
