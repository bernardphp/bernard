<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PrefetchMessageCache;

class PrefetchMessageCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testPushesAndPop()
    {
        $cache = new PrefetchMessageCache;
        $cache->push('my-queue', array('message1', 'r0'));

        $this->assertEquals(array('message1', 'r0'), $cache->pop('my-queue'));
        $this->assertInternalType('null', $cache->pop('my-queue'));
    }
}
