<?php

namespace Bernard\Tests\Serializer;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Tests\Fixtures;

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
        $json = '{"args":{"name":"SendNewsletter"},"class":"Bernard:Message:DefaultMessage","timestamp":' . time() . '}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter')));

        $json = '{"args":{"name":"SendNewsletter","newsletterId":1,"users":["henrikbjorn"]},"class":"Bernard:Message:DefaultMessage","timestamp":' . time() . '}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter', array(
            'newsletterId' => 1,
            'users' => array(
                'henrikbjorn'
            ),
        ))));
    }

    public function testItSerializesACustomImplementedMessage()
    {
        $json = '{"args":{"newsletterId":10},"class":"Bernard:Tests:Fixtures:SendNewsletterMessage","timestamp":' . time() . '}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new Fixtures\SendNewsletterMessage())));
    }

    public function testItDeserializesACustomImplementedMessage()
    {
        $json = '{"args":{"newsletterId":10},"class":"Bernard:Tests:Fixtures:SendNewsletterMessage","timestamp":' . time() . '}';
        $envelope = $this->serializer->deserialize($json);

        $this->assertInstanceOf('Bernard\Tests\Fixtures\SendNewsletterMessage', $envelope->getMessage());
    }

    public function testItDeserializesAnUnknownClass()
    {
        $time = time();

        $json = '{"args":{"meaningOfLife":42},"class":"UnknownNamespace:UnknownMessage","timestamp":' . $time . '}';
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
