<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PrefetchMessageCache;

class PrefetchMessageCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testPushesAndPop()
    {
        $driverMessage = new \Bernard\DriverMessage('message1', 'r0');

        $cache = new PrefetchMessageCache();
        $cache->push('my-queue', $driverMessage);

        $this->assertEquals($driverMessage, $cache->pop('my-queue'));
        $this->assertNull($cache->pop('my-queue'));
    }
}
