<?php

namespace Bernard\Tests;

use Bernard\Consumer;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testTrapMarksShutdown()
    {
        $consumer = new Consumer($this->getMock('Bernard\ServiceResolver'));
        $consumer->trap(1);

        $r = new \ReflectionProperty($consumer, 'shutdown');
        $r->setAccessible(true);

        $this->assertTrue($r->getValue($consumer));
    }
}
