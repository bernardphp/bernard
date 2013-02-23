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
        $manager->register('SendAnotherEmail', $class2 = new \stdClass);

        $this->assertSame($class, $manager->getService('SendNewsletter'));
        $this->assertSame($class2, $manager->getService('sendanotheremail'));
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
        $message2 = new DefaultMessage('registerUser');

        $mock = $this->getMock('stdClass', array('onSendNewsletter', 'onRegisterUser'));
        $mock->expects($this->at(0))->method('onSendNewsletter')->with($this->equalTo($message));
        $mock->expects($this->at(1))->method('onRegisterUser')->with($this->equalTo($message2));

        $manager = new WorkerManager;
        $manager->register('SendNewsletter', $mock);
        $manager->register('RegisterUser', $mock);

        $manager->handle($message);
        $manager->handle($message2);
    }
}
