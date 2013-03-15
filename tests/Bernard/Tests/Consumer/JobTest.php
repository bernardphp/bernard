<?php

namespace Bernard\Tests\Consumer;

use Bernard\Message\DefaultMessage;
use Bernard\Consumer\Job;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testItInvokesServiceObject()
    {
        $message = new DefaultMessage('SendNewsletter');

        $service = $this->getMock('stdClass', array('onSendNewsletter'));
        $service->expects($this->exactly(2))->method('onSendNewsletter')->with($this->equalTo($message));

        $job = new Job($service, $message);
        $job->invoke();

        $job();
    }
}
