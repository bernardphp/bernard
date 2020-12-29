<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PrefetchMessageCache;

class PrefetchMessageCacheTest extends \PHPUnit\Framework\TestCase
{
    public function testPushesAndPop()
    {
        $cache = new PrefetchMessageCache();
        $cache->push('my-queue', ['message1', 'r0']);

        $this->assertEquals(['message1', 'r0'], $cache->pop('my-queue'));
        $this->assertNull($cache->pop('my-queue'));
    }
}
