<?php
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

namespace BachPedersen\ImageStore;


use BachPedersen\ImageStore\Model\ImageRaw;
use BachPedersen\ImageStore\Model\ImageSize;
use Intervention\Image\Image;
use Log;
use Riak\Bucket;
use Riak\Input\GetInput;
use Riak\Input\PutInput;
use Riak\Object;

class RiakImageStoreRepository implements ImageStoreRepository
{

    /**
     * @var \Riak\Bucket
     */
    private $imageBucket;
    /**
     * @var \Riak\Bucket
     */
    private $imageResizedBucket;

    public function __construct(Bucket $imageBucket, Bucket $imageResizedBucket)
    {
        $this->imageBucket = $imageBucket;
        $this->imageResizedBucket = $imageResizedBucket;
    }

    /**
     * @inheritdoc
     */
    public function storeImageInRiak(Image $image, $name, $sizes = [], $saveOriginal = true)
    {
        if ($saveOriginal) {
            $this->storeImage($image, $this->imageBucket, $name);
        }
        /** @var $sizes ImageSize[] */
        foreach ($sizes as $size) {
            $image->backup();
            $image->resize($size->width, $size->height, true);
            $this->storeImage($image, $this->imageResizedBucket, $size->toString().$name);
            $image->reset();
        }
    }

    /**
     * @inheritdoc
     */
    public function getImage($name, ImageSize $withSize = null)
    {
        if (isset($withSize)) {
            $key = $withSize->toString().$name;
            return $this->getImageWithKey($this->imageResizedBucket, $key);
        } else {
            return $this->getImageWithKey($this->imageBucket, $name);
        }
    }

    /**
     * @param Bucket $bucket
     * @param string $key
     * @return ImageRaw|null
     */
    private function getImageWithKey(Bucket $bucket, $key)
    {
        $getOutput = $bucket->get($key);
        if ($getOutput->hasObject()) {
            $object = $getOutput->getObject();
            if (!$object->isDeleted()) {
                $content = $object->getContent();
                $contentType = $object->getContentType();
                return new ImageRaw($content, $contentType);
            }
        }
        return null;
    }

    /**
     * @param Image $image
     * @param Bucket $bucket
     * @param string $key
     */
    private function storeImage(Image $image, Bucket $bucket, $key)
    {
        $data = $image->encode();
        if (isset($data)) {
            $options = new GetInput();
            $options->setNotFoundOk(true);
            $options->setReturnHead(true);
            $getOutput = $bucket->get($key, $options);
            if ($getOutput->hasObject()) {
                $imageObj = $getOutput->getFirstObject();
            } else {
                $imageObj = new Object($key);
            }
            $imageObj->setContentType($image->mime);
            $imageObj->setContent($data);
            $bucket->put($imageObj);
        }
    }
}