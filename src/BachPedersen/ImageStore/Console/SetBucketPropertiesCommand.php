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

namespace BachPedersen\RiakImageStore\Console;

use Illuminate\Console\Command;
use Riak\BucketPropertyList;
use Riak\Connection;

/**
 * Class SetBucketPropertiesCommand
 * @package BachPedersen\RiakImageStore\Console
 */
class SetBucketPropertiesCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'imagestore:buckets:init';

    /**
     * @var string[]
     */
    private $bucketNames;

    /**
     * @var \Riak\Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     * @param string[] $bucketNames
     */
    public function __construct(Connection $connection, $bucketNames = array())
    {
        parent::__construct();
        $this->bucketNames = $bucketNames;
        $this->connection = $connection;
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $props = new BucketPropertyList();
        $props->setAllowMult(false)
            ->setLastWriteWins(false)
            ->setBasicQuorum(true);
        foreach ($this->bucketNames as $bucketName) {
            $bucket = $this->connection->getBucket($bucketName);
            $bucket->setPropertyList($props);
            $this->info("Bucket properties opdated for $bucketName");
        }
    }

} 