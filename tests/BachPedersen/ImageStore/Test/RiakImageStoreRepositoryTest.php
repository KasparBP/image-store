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

namespace BachPedersen\ImageStore\Test;

use BachPedersen\ImageStore\Model\ImageRaw;
use BachPedersen\ImageStore\Model\ImageSize;
use BachPedersen\ImageStore\RiakImageStoreRepository;
use Intervention\Image\Image;

class RiakImageStoreRepositoryTest extends \PHPUnit_Framework_TestCase
{
    const TEST_IMAGE_BUCKET_NAME = 'imagestore.image.unittest';
    const TEST_IMAGE_RESIZED_BUCKET_NAME = 'imagestore.image_resized.unittest';

    /**
     * @var \Riak\Connection
     */
    private $connection;

    /**
     * @var \BachPedersen\ImageStore\ImageStoreRepository
     */
    private $imageStore;

    public function setUp()
    {
        $this->connection = new \Riak\Connection('localhost');
        $this->imageStore = new RiakImageStoreRepository(
            $this->connection->getBucket(static::TEST_IMAGE_BUCKET_NAME),
            $this->connection->getBucket(static::TEST_IMAGE_RESIZED_BUCKET_NAME)
        );
    }

    public function testNotFound()
    {
        $imageRaw = $this->imageStore->getImage('not found .. i hope');
        $this->assertNull($imageRaw);
    }

    public function testNoResizing()
    {
        $imagePath = __DIR__."/Fixtures/countryside_400x267.jpg";
        $image = new Image($imagePath);
        $image->mime = 'image/png';
        $name = 'countryside';
        $this->imageStore->storeImageInRiak($image, $name);

        $imageRaw = $this->imageStore->getImage($name);
        $this->assertNotNull($imageRaw);
        $this->assertEquals('image/png', $imageRaw->mimeType);
    }

    public function testResizePortrait()
    {
        $imagePath = __DIR__.'/Fixtures/icicles_265x400.jpg';
        $image = new Image($imagePath);
        $image->mime = 'image/png';
        $name = 'icicles';

        $size1 = new ImageSize(100,100);
        $size2 = new ImageSize(500,500);

        $this->imageStore->storeImageInRiak($image, $name, [$size1, $size2]);

        $imageRaw = $this->imageStore->getImage($name, $size1);
        $this->assertNotNull($imageRaw);
        $decoded1 = $this->imageFromRaw($imageRaw);
        $this->assertEquals(100, $decoded1->height);
        $this->assertEquals(100, $decoded1->width);

        $imageRaw = $this->imageStore->getImage($name, $size2);
        $this->assertNotNull($imageRaw);
        $decoded2 = $this->imageFromRaw($imageRaw);
        $this->assertEquals(500, $decoded2->height);
        $this->assertEquals(500, $decoded2->width);
    }

    private function imageFromRaw(ImageRaw $raw) {
        return new Image($raw->data);
    }

} 