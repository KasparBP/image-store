<?php namespace BachPedersen\ImageStore\Controllers;
/*
   Copyright 2014: Kaspar Bach Pedersen

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

     http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
*/

use App;
use BachPedersen\ImageStore\Model\ImageSize;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class ImageStoreController extends Controller
{
    /**
     * @var \BachPedersen\ImageStore\ImageStoreRepository
     */
    private $imageStore;

    public function __construct()
    {
        $this->imageStore = App::make('imagestore');
    }

    public function showImage($imageName)
    {
        $rawImage = $this->imageStore->getImage($imageName);
        return $this->respondWithImage($rawImage);
    }

    public function showImageInSize($imageName, $width, $height)
    {
        $rawImage = $this->imageStore->getImage($imageName, new ImageSize($width, $height));
        return $this->respondWithImage($rawImage);
    }

    /**
     * @param $rawImage
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function respondWithImage($rawImage)
    {
        if (isset($rawImage) && isset($rawImage->data)) {
            return Response::stream(function () use ($rawImage) {
                echo $rawImage->data;
            }, 200, ['content-type' => $rawImage->mimeType, 'content-transfer-encoding' => 'binary']);
        } else {
            App::abort(404);
        }
    }
} 