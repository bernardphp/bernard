<?php

namespace Bernard\Tests;

use Bernard\Consumer;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testNameIsHostnameAndPid()
    {
        $consumer = new Consumer($this->getMock('Bernard\ServiceResolver'));

        $this->assertEquals(gethostname() . ':' . getmypid(), $consumer->getName());
    }
}
