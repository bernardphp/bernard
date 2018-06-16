<?php

namespace Bernard\Tests\Driver\Pheanstalk;

use Bernard\Driver\Pheanstalk\Driver;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;

class DriverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $pheanstalk;

    /** @var Driver */
    private $driver;

    public function setUp()
    {
        $this->pheanstalk = $this->getMockBuilder(Pheanstalk::class)
            ->setMethods([
                'listTubes',
                'statsTube',
                'putInTube',
                'reserveFromTube',
                'delete',
                'stats',
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new Driver($this->pheanstalk);
    }

    public function testItExposesInfo()
    {
        $driver = new Driver($this->pheanstalk);

        $info = new \ArrayObject(['info' => true]);

        $this->pheanstalk->expects($this->once())->method('stats')
            ->will($this->returnValue($info));

        $this->assertEquals(['info' => true], $driver->info());
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf(\Bernard\Driver::class, $this->driver);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->pheanstalk->expects($this->once())->method('statsTube')
            ->with($this->equalTo('send-newsletter'))->will($this->returnValue(['current-jobs-ready' => 4]));

        $this->assertEquals(4, $this->driver->countMessages('send-newsletter'));
    }

    public function testItListQueues()
    {
        $queues = [
            'failed',
            'queue1',
        ];

        $this->pheanstalk->expects($this->once())->method('listTubes')
            ->will($this->returnValue($queues));

        $this->assertEquals($queues, $this->driver->listQueues());
    }

    public function testAcknowledgeMessage()
    {
        $this->pheanstalk->expects($this->once())->method('delete')
            ->with($this->isInstanceOf(Job::class));

        $this->driver->acknowledgeMessage('my-queue', new Job(1, null));
    }

    public function testItPeeksInAQueue()
    {
        $this->assertEquals([], $this->driver->peekQueue('my-queue2'));
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

        $this->assertEquals(['message1', $job1], $this->driver->popMessage('my-queue1'));
        $this->assertEquals(['message2', $job2], $this->driver->popMessage('my-queue2'));
        $this->assertEquals([null, null], $this->driver->popMessage('my-queue2'));
    }
}
