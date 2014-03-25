<?php namespace BachPedersen\ImageStore;
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

use BachPedersen\LaravelRiak\Console\BucketInitCommand;
use BachPedersen\ImageStore\Console\SetBucketPropertiesCommand;
use Illuminate\Support\ServiceProvider;
use Riak\BucketPropertyList;
use Riak\Connection;

class ImageStoreServiceProvider extends ServiceProvider
{

    public static $CONFIG_NAME_IMAGESTORE_BUCKET = 'imagestore.bucket';
    public static $CONFIG_NAME_IMAGESTORE_RESIZEBUCKET = 'imagestore.bucket_resize';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('imagestore', function($app)
        {
            /** @var $connection Connection */
            $connection = $app['riak'];
            $imageBucket = $app['config'][static::$CONFIG_NAME_IMAGESTORE_BUCKET];
            $imageResizedBucket = $app['config'][static::$CONFIG_NAME_IMAGESTORE_RESIZEBUCKET];
            return new RiakImageStoreRepository($connection->getBucket($imageBucket),
                $connection->getBucket($imageResizedBucket));
        });
        $this->registerCommands();
    }

    /**
     * Register the cache related console commands.
     *
     * @return void
     */
    public function registerCommands()
    {
        $this->app['command.imagestore.bucket'] = $this->app->share(function($app)
        {
            $imageBucket = $app['config'][static::$CONFIG_NAME_IMAGESTORE_BUCKET];
            $imageResizedBucket = $app['config'][static::$CONFIG_NAME_IMAGESTORE_RESIZEBUCKET];
            return new SetBucketPropertiesCommand($app['riak'], [$imageBucket, $imageResizedBucket]);
        });

        $this->commands('command.imagestore.bucket');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('imagestore');
    }

}
