<?php

namespace Raekke\Tests;

use Raekke\Message\DefaultMessage;
use Raekke\WorkerManager;

class WorkerManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsExceptionWhenServiceIsMissing()
    {
        $this->setExpectedException('Raekke\Exception\MissingServiceException');

        $manager = new WorkerManager;
        $manager->register('SendNewsletter', $this->getMock('stdClass'));
        $manager->getService('NotSendNewsletter');
    }

    public function testItHoldsServices()
    {
        $manager = new WorkerManager;
        $manager->register('SendNewsletter', $class = new \stdClass);

        $this->assertSame($class, $manager->getService('SendNewsletter'));
    }

    public function testItThrowsExceptionWhenMethodIsUncallable()
    {
        $this->setExpectedException('Raekke\Exception\UncallableMethodException');

        $manager = new WorkerManager;
        $manager->register('SendNewsletter', $this->getMock('stdClass'));
        $manager->handle(new DefaultMessage('SendNewsletter'));
    }

    public function testItInvokesMethodOnServiceWithMessageAsParameter()
    {
        $message = new DefaultMessage('SendNewsletter');

        $mock = $this->getMock('stdClass', array('onSendNewsletter'));
        $mock->expects($this->once())->method('onSendNewsletter')->with($this->equalTo($message));

        $manager = new WorkerManager;
        $manager->register('SendNewsletter', $mock);

        $manager->handle($message);
    }
}
