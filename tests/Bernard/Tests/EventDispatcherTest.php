<?php

namespace Bernard\Tests;

use Bernard\EventDispatcher;

class EventDispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testRegisterCallsRegisterOnSubscriber()
    {
        $dispatcher = new EventDispatcher;

        $subscriber = $this->getMock('Bernard\EventSubscriber');
        $subscriber->expects($this->once())->method('subscribe')->with($dispatcher);


        $dispatcher->subscribe($subscriber);
    }
}
