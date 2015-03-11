<?php

namespace spec\Bernard\Driver;

use google\appengine\api\taskqueue\PushTask;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class AppEngineDriverSpec extends ObjectBehavior
{
    function let()
    {
        class_exists('google\appengine\api\taskqueue\PushTask') or class_alias('Bernard\Stub\PushTask', 'google\appengine\api\taskqueue\PushTask');
        // PushTask::$messages = array();

        $this->beConstructedWith(array('send-newsletter' => '/url_endpoint'));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Driver\AppEngineDriver');
    }

    function it_is_a_driver()
    {
        $this->shouldImplement('Bernard\Driver');
    }

    function it_lists_queues()
    {
        $this->listQueues()->shouldReturn(array('/url_endpoint' => 'send-newsletter'));
    }

    function it_pushes_a_message_to_an_endpoint()
    {
        $this->pushMessage('send-newsletter', 'message');
    }

    function it_pushes_a_message_to_a_default_endpoint()
    {
        $this->pushMessage('default', 'message');
    }

    function it_is_not_peekable()
    {
        $this->peekQueue('queue1')->shouldReturn(array());
    }

    function it_does_not_provide_any_info()
    {
        $this->info()->shouldReturn(array());
    }
}
