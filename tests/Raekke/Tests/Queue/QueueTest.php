<?php

namespace Raekke\Tests\Queue;

use Raekke\Queue\Queue;

class QueueTest extends \PHPUnit_Framework_TestCase
{
    public function testKeyIsPrefixedWithQueue()
    {
        $connection = $this->getMockBuilder('Raekke\Connection')->disableOriginalConstructor()->getMock();
        $queue = new Queue('send-newsletter', $connection, $this->getMock('Raekke\Serializer\SerializerInterface'));

        $this->assertEquals('queue:send-newsletter', $queue->getKey());
    }
}
