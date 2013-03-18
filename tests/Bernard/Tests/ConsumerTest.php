<?php

namespace Bernard\Tests;

use Bernard\Consumer;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function testStringRepresentation()
    {
        $consumer = new Consumer($this->getMock('Bernard\ServiceResolver'));
        $id = gethostname() . ':' . getmypid();

        $this->assertEquals($id, (string) $consumer);
    }
}
