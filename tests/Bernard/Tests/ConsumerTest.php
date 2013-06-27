<?php

namespace Bernard\Tests;

use Bernard\Consumer;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testShutdown()
    {
        $consumer = new Consumer($this->getMock('Bernard\ServiceResolver'));
        $consumer->shutdown();

        $r = new \ReflectionProperty($consumer, 'shutdown');
        $r->setAccessible(true);

        $this->assertTrue($r->getValue($consumer));
    }
}
