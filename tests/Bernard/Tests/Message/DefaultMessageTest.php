<?php

namespace Bernard\Tests\Message;

use Bernard\Message\DefaultMessage;

class DefaultMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testItSupportsDynamicProperties()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->assertObjectNotHasAttribute('newsletterId', $message);

        $message = new DefaultMessage('SendNewsletter', array(
            'newsletterId' => 12,
        ));

        $this->assertObjectHasAttribute('newsletterId', $message);
        $this->assertEquals(12, $message->newsletterId);
    }

    public function testItHaveAName()
    {
        $message = new DefaultMessage('SendNewsletter');

        $this->assertObjectHasAttribute('name', $message);
        $this->assertEquals('SendNewsletter', $message->getName());

        $message = new DefaultMessage('SendNewsletter', array(
            'name' => 'NotSendNewsletter',
        ));

        $this->assertEquals('SendNewsletter', $message->getName());
    }

    /**
     * @dataProvider dataProviderNames
     */
    public function testItNormalizesName($original, $normalized)
    {
        $message = new DefaultMessage($original);
        $this->assertEquals($normalized, $message->getName());
    }

    public function dataProviderNames()
    {
        return array(
            array('Send Newsletter', 'SendNewsletter'),
            array('1Send Newsletter', 'SendNewsletter'),
            array('Send_Newsletter', 'Send_Newsletter'),
        );
    }
}
