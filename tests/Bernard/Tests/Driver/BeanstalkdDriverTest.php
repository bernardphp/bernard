<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\BeanstalkdDriver;

/**
 * @author Antoine Corcy <contact@sbin.dk>
 */
class BeanstalkdDriverTest extends \PHPUnit_Framework_TestCase
{
    protected $beanstalkd;
    protected $driver;

    protected function setUp()
    {
        $this->beanstalkd = $this->getMockBuilder('Pheanstalk_Pheanstalk')
            ->setMethods(array(
                'statsTube',
                'useTube',
                'delete',
                'peekReady',
                'peek',
                'listTubes',
                'putInTube',
                'stats',
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->driver = new BeanstalkdDriver($this->beanstalkd);
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->driver);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->beanstalkd
            ->expects($this->once())
            ->method('statsTube')
            ->with($this->equalTo('my-queue'))
            ->will($this->returnValue((object) array('total_jobs' => 4)));

        $this->assertEquals(4, $this->driver->countMessages('my-queue'));
    }

    public function testItListQueues()
    {
        $beanstalkdQueues = array('failed', 'queue1');

        $this->beanstalkd
            ->expects($this->once())
            ->method('listTubes')
            ->will($this->returnValue($beanstalkdQueues));

        $this->assertEquals($beanstalkdQueues, $this->driver->listQueues());
    }

    public function testCreateQueue()
    {
        $this->beanstalkd
            ->expects($this->once())
            ->method('useTube')
            ->with($this->equalTo('my-queue'));

        $this->driver->createQueue('my-queue');
    }

    public function testItRemovesAQueue()
    {
        $this->beanstalkd
            ->expects($this->once())
            ->method('delete')
            ->will($this->throwException(new \Pheanstalk_Exception_ServerException));

        $this->driver->removeQueue('my-queue');
    }

    public function testItPushesMessages()
    {
        $this->beanstalkd
            ->expects($this->once())
            ->method('putInTube')
            ->with($this->equalTo('my-queue'), $this->equalTo('This is a message'));

        $this->driver->pushMessage('my-queue', 'This is a message');
    }

    public function testItPopMessages()
    {
        $this->beanstalkd
            ->expects($this->at(0))
            ->method('peekReady')
            ->with($this->equalTo('my-queue'))
            ->will($this->returnValue(new \Pheanstalk_Job(1, 'job_1')));

        $this->beanstalkd
            ->expects($this->at(1))
            ->method('peekReady')
            ->with($this->equalTo('my-queue-2'))
            ->will($this->returnValue(new \Pheanstalk_Job(2, 'job_2')));

        $this->beanstalkd
            ->expects($this->at(2))
            ->method('peekReady')
            ->with($this->equalTo('my-queue-2'))
            ->will($this->throwException(new \Exception));

        $this->assertEquals(array('job_1', 1), $this->driver->popMessage('my-queue'));
        $this->assertEquals(array('job_2', 2), $this->driver->popMessage('my-queue-2'));
        $this->assertEquals(array(null, null), $this->driver->popMessage('my-queue-2'));
    }

    public function testAcknowledgeMessage()
    {
        $this->beanstalkd
            ->expects($this->once())
            ->method('delete');

        $this->driver->acknowledgeMessage('my-queue', 123);
    }
}
