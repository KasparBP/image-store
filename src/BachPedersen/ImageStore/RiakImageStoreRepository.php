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

namespace BachPedersen\RiakImageStore;


use BachPedersen\RiakImageStore\Model\ImageSize;
use Intervention\Image\Image;
use Riak\Bucket;

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

    /** Save image, and save resized images as well if sizes are provided.
     * @param Image $image
     * @param string $name
     * @param ImageSize[] $sizes
     */
    public function storeImageInRiak(Image $image, $name, $sizes = array())
    {
        // TODO: Implement storeImageInRiak() method.
    }

    /** Get an image with specified size
     * @param string $name
     * @param ImageSize $withSize
     * @return string|null raw data string or null
     */
    public function getImage($name, ImageSize $withSize = null)
    {
        // TODO: Implement getImage() method.
    }
}