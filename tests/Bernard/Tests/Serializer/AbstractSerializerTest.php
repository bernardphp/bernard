<?php

namespace Bernard\Tests\Serializer;

use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;

require __DIR__ . '/../Fixtures/SendNewsletterMessage.php';

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
        $json = '{"args":{"newsletterId":10},"class":"SerializerApplication:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new \SerializerApplication\SendNewsletterMessage())));
    }

    public function testItDeserializesACustomImplementedMessage()
    {
        $json = '{"args":{"newsletterId":10},"class":"SerializerApplication:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $envelope = $this->serializer->deserialize($json);

        $this->assertInstanceOf('SerializerApplication\SendNewsletterMessage', $envelope->getMessage());
    }

    public function testItDeserializesAnUnknownClass()
    {
        $time = time();

        $json = '{"args":{"meaningOfLife":42},"class":"UnknownNamespace:UnknownMessage","timestamp":' . $time . ',"retries":0}';
        $envelope = $this->serializer->deserialize($json);

        $this->assertInstanceOf('Bernard\Message\DefaultMessage', $envelope->getMessage());
        $this->assertEquals('UnknownNamespace\\UnknownMessage', $envelope->getClass());
        $this->assertEquals('UnknownMessage', $envelope->getMessage()->getName());
        $this->assertEquals(42, $envelope->getMessage()->meaningOfLife);
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
