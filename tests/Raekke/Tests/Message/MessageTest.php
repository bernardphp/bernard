<?php

namespace Raekke\Tests\Message;

class MessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProviderClassNames
     */
    public function testItUsesClassNameAsNameAndQueueNameNormalized($mockClassName, $messageName, $queueName)
    {
        $message = $this->getMockForAbstractClass('Raekke\Message\Message', array(), $mockClassName);

        $this->assertEquals($messageName, $message->getName());
        $this->assertEquals($queueName, $message->getQueue());
    }

    public function dataProviderClassNames()
    {
        return array(
            array('SendNewsletter', 'SendNewsletter', 'send-newsletter'),
            array('SendNewsletterMessage', 'SendNewsletter', 'send-newsletter'),
        );
    }
}
