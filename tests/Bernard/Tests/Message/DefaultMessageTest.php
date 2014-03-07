<?php

namespace Bernard\Tests\Message;

use Bernard\Message\DefaultMessage;

class DefaultMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testItHaveAName()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->assertEquals('SendNewsletter', $message->getName());
    }

    public function testItHasArguments()
    {
        $message = new DefaultMessage('SendNewsletter', array(
            'key1' => 1,
            'key2' => array(1,2,3,4),
            'key3' => null,
        ));

        $this->assertTrue(isset($message['key1']));
        $this->assertTrue(isset($message['key1']));

        $this->assertEquals(1, $message['key1']);
        $this->assertEquals(array(1,2,3,4), $message['key2']);
        $this->assertInternalType('null', $message['key3']);

    }

    public function testItImplementsArrayAccess()
    {
        $this->assertInstanceOf('ArrayAccess', new DefaultMessage('SendNewsletter'));
    }
}
