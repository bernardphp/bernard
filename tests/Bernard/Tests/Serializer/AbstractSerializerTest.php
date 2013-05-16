<?php

namespace Bernard\Tests\Serializer;

use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;

abstract class AbstractSerializerTest extends \PHPUnit_Framework_TestCase
{
    abstract public function createSerializer();

    public function setUp()
    {
        $this->serializer = $this->createSerializer();
    }

    public function testImplementsSerializer()
    {
        $this->assertInstanceOf('Bernard\Serializer', $this->serializer);
    }

    public function testItSerializesDefaultMessage()
    {
        $json = '{"args":{"name":"SendNewsletter"},"class":"Bernard:Message:DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter')));

        $json = '{"args":{"name":"SendNewsletter","newsletterId":1,"users":["henrikbjorn"]},"class":"Bernard:Message:DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter', array(
            'newsletterId' => 1,
            'users' => array(
                'henrikbjorn'
            ),
        ))));
    }

    public function testItSerializesACustomImplementedMessage()
    {
        $json = '{"args":{},"class":"SymfonySerializerApplication:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new \SymfonySerializerApplication\SendNewsletterMessage())));
    }

    public function testItDeserializesACustomImplementedMessage()
    {
        $json = '{"args":{},"class":"SymfonySerializerApplication:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new \SymfonySerializerApplication\SendNewsletterMessage())));
    }

    public function testItDeserializesDefaultMessage()
    {
        $message = $this->createWrappedDefaultMessage('SendNewsletter');
        $json = $this->serializer->serialize($message);

        $this->assertEquals($message, $this->serializer->deserialize($json));
    }

    public function createWrappedDefaultMessage($name, array $properties = array())
    {
        return new Envelope(new DefaultMessage($name, $properties));
    }
}

namespace SymfonySerializerApplication;
class SendNewsletterMessage extends \Bernard\Message\AbstractMessage {}
