<?php

namespace Raekke\Tests\Consumer;

use Raekke\Message\DefaultMessage;
use Raekke\Consumer\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testItInvokesServiceObject()
    {
        $message = new DefaultMessage('SendNewsletter');

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->once())->method('onSendNewsletter')->with($this->equalTo($message));

        $job = new Job($service, $message);
        $job->invoke();
    }
}
