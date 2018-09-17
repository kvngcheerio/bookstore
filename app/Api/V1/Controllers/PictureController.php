<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Api\V1\Models\Book;
use Illuminate\Support\Facades\Input;
use App\Api\V1\Models\Picture;
use App\Http\Controllers\Controller;
use Dingo\Api\Exception\DeleteResourceFailedException;
use Illuminate\Support\Facades\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Exception;

class PictureController extends Controller
{
    /**
     * show/download an image
     */
    public function show($pictureUrl)
    {
        try {
            $pictureUrl = str_after($pictureUrl, 'public');
            $pictureUrl = public_path() .'/storage'. $pictureUrl;
            return HttpResponse::download($pictureUrl);
        } catch (Exception $e) {
            throw new NotFoundHttpException('picture not found');
        }
    }

    /**
    * store a picture
    *
    * @param Illuminate\Http\Request $request
    *
    * @return Response
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'picture' => 'required|image|max:2048'
             ]);
        $picture = Picture::getPicture($request);
        
        if ($pictures = Picture::savePicture($picture)) {
            return new Response($pictures, 201);
        }
        throw new StoreResourceFailedException('couldn\'t upload picture');
    }

    /**
    * delete a picture
    *
    * @param Illuminate\Http\Request $request
    * @param string $picturePath
    *
    * @return Response
    */
    public function destroy($picturePath)
    {
        $path = $picturePath;
        if (!$picture = Picture::where('path', $path)->first()) {
            throw new DeleteResourceFailedException('picture not found');
        }
        if (Picture::deletePicture($picture)) {
            return new Response(['status'=>  'picture deleted' ], 201);
        }

        throw new DeleteResourceFailedException('picture delete request failed');
    }

        
    /**
     * delete old pictures that are not attached
     */
    public function deleteOldPictures(Request $request) {
        
        $oldPictures = Picture::getOldPictures();

    }

}
