<?php

namespace App\Api\V1\Traits;

use App\Api\V1\Models\Picture;
use Exception;

trait HasPictures
{
    protected $uploadDir = null;

    protected $maxPictures = 5; //maximum number of pictures a good item is allowed

    public function pictures()
    {
        return $this->belongsToMany('App\Api\V1\Models\Picture');
    }

    /**
     * check if model has more than 5 pictures
     *
     */
    public function canAddMorePictures()
    {
        $query = $this->pictures()->get();
        if ($query->count() && ($query->count() >= $this->maxPictures)) {
            //we can't add more pictures
            return false;
        }
        return true;
    }
    
    /**
     * attach pictures to the desired object. (i.e upload to directory and link picture pivot table)
     *
     * @param file | file array $pictures
     * @param string $pictureName
     * @param string $dir
     *
     * @return bool | $pictures
     */
    public function attachPictures($pictures, string $pictureName, string $dir = null)
    {
        if (!$this->canAddMorePictures()) {
            return false;
        }
        $dir = is_null($this->uploadDir)? $dir : $this->uploadDir;
        
        if (!$pictures = Picture::savePicture($pictures, $pictureName, $dir)) {
            return false;
        }

        //why we use sync and false as 2nd parameter: https://stackoverflow.com/a/24706638/4625623
        $this->pictures()->sync($pictures, false);

        return $pictures;
    }
    /**
     * detach pictures from the desired object. (i.e delete pictures from dir and unlink in picture pivot table)
     *
     * @param App\Api\V1\Models\Picture $pictures
     *
     * @return mixed
     */
    public function detachPictures($pictures)
    {
        if (!$deletePictures = Picture::deletePicture($pictures)) {
            return false;
        }    
        $this->pictures()->detach($pictures);

        return $deletePictures;
    }

    /**
     * check if the model has pictures
     *
     * @return mixed
     */
    public function hasPictureInDb()
    {
        $return = false;
        try {
            $return = $this->pictures()->get();
            if($return->count()) {
                $return = $return; 
            } else {
                $return = false;
            }
        } catch (Exception $e) {
            $return = false;
        }
        //if model has pictures we return the picture object
        return $return;
    }
}
