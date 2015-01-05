<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PheanstalkDriver;
use Pheanstalk\Job;

class PheanstalkDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pheanstalk = $this->getMockBuilder('Pheanstalk\Pheanstalk')
            ->setMethods(array(
                'listTubes',
                'statsTube',
                'putInTube',
                'reserveFromTube',
                'delete',
                'stats',
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new PheanstalkDriver($this->pheanstalk);
    }

    public function testItExposesInfo()
    {
        $driver = new PheanstalkDriver($this->pheanstalk);

        $info = new \ArrayObject(array('info' => true));

        $this->pheanstalk->expects($this->once())->method('stats')
            ->will($this->returnValue($info));

        $this->assertEquals(array('info' => true), $driver->info());
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->driver);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->pheanstalk->expects($this->once())->method('statsTube')
            ->with($this->equalTo('send-newsletter'))->will($this->returnValue(array('current-jobs-ready' => 4)));

        $this->assertEquals(4, $this->driver->countMessages('send-newsletter'));
    }

    public function testItListQueues()
    {
        $queues = array(
            'failed',
            'queue1',
        );

        $this->pheanstalk->expects($this->once())->method('listTubes')
            ->will($this->returnValue($queues));

        $this->assertEquals($queues, $this->driver->listQueues());
    }

    public function testAcknowledgeMessage()
    {
        $this->pheanstalk->expects($this->once())->method('delete')
            ->with($this->isInstanceOf('Pheanstalk\Job'));

        $this->driver->acknowledgeMessage('my-queue', new Job(1, null));
    }

    public function testItPeeksInAQueue()
    {
        $this->assertEquals(array(), $this->driver->peekQueue('my-queue2'));
    }

    public function testItPushesMessages()
    {
        $this->pheanstalk
            ->expects($this->once())
            ->method('putInTube')
            ->with($this->equalTo('my-queue'), $this->equalTo('This is a message'));

        $this->driver->pushMessage('my-queue', 'This is a message');
    }

    public function testItPopMessages()
    {
        $this->pheanstalk
            ->expects($this->at(0))
            ->method('reserveFromTube')
            ->with($this->equalTo('my-queue1'), $this->equalTo(5))
            ->will($this->returnValue($job1 = new Job(1, 'message1')));
        $this->pheanstalk
            ->expects($this->at(1))
            ->method('reserveFromTube')
            ->with($this->equalTo('my-queue2'), $this->equalTo(5))
            ->will($this->returnValue($job2 = new Job(2, 'message2')));
        $this->pheanstalk
            ->expects($this->at(1))
            ->method('reserveFromTube')
            ->with($this->equalTo('my-queue2'), $this->equalTo(5))
            ->will($this->returnValue(null));

        $this->assertEquals(array('message1', $job1), $this->driver->popMessage('my-queue1'));
        $this->assertEquals(array('message2', $job2), $this->driver->popMessage('my-queue2'));
        $this->assertEquals(array(null, null), $this->driver->popMessage('my-queue2'));
    }
}
