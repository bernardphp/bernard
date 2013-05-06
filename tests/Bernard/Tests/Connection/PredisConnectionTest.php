<?php

namespace Bernard\Tests\Connection;

use Bernard\Connection\PredisConnection;

class PredisConnectionTest extends PhpRedisConnectionTest
{
    public function setUp()
    {
        // Because predis uses __call all methods that needs mocking must be
        // explicitly defined.
        $this->redis = $this->getMock('Predis\Client', array(
            'lLen',
            'sMembers',
            'lRange',
            'blPop',
            'sRemove',
            'del',
            'sAdd',
            'sContains',
            'rPush',
            'sRem',
        ));

        $this->connection = new PredisConnection($this->redis);
    }
}
