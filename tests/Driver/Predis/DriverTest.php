<?php

namespace Bernard\Tests\Driver\Predis;

use Bernard\Driver\Predis\Driver;
use Predis\Client;

class DriverTest extends \Bernard\Tests\Driver\PhpRedis\DriverTest
{
    public function setUp()
    {
        // Because predis uses __call all methods that needs mocking must be
        // explicitly defined.
        $this->redis = $this->getMockBuilder(Client::class)->setMethods(array(
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
        ))->getMock();

        $this->connection = new Driver($this->redis);
    }

    public function testItPopMessages()
    {
        $this->redis->expects($this->at(0))->method('blPop')->with($this->equalTo('queue:send-newsletter'))
            ->will($this->returnValue(array('my-queue', 'message1')));

        $this->redis->expects($this->at(1))->method('blPop')->with($this->equalTo('queue:ask-forgiveness'), $this->equalTo(30))
            ->will($this->returnValue(array('my-queue2', 'message2')));

        $this->assertEquals(array('message1', null), $this->connection->popMessage('send-newsletter'));
        $this->assertEquals(array('message2', null), $this->connection->popMessage('ask-forgiveness', 30));
    }
}
