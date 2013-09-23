<?php

namespace Bernard\Tests\Serializer;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Serializer\SimpleSerializer;
use Bernard\Tests\Fixtures;

class SimpleSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = new SimpleSerializer();
    }

    public function testItIsASerializer()
    {
        $this->assertInstanceOf('Bernard\Serializer', $this->serializer);
    }

    public function testItOnlySupportsDefaultMessage()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->serializer->serialize(new Envelope(new Fixtures\SendNewsletterMessage()));
    }

    /**
     * @dataProvider provideDefaultMessage
     */
    public function testItSerializesDefaultMessage($properties)
    {
        $expected = array(
            'args'      => array('name' => 'SendNewsletter') + $properties,
            'class'     => 'Bernard:Message:DefaultMessage',
            'timestamp' => time(),
        );

        $envelope = new Envelope(new DefaultMessage('SendNewsletter', $properties));
        $this->assertEquals(json_encode($expected), $this->serializer->serialize($envelope));
    }

    public function provideDefaultMessage()
    {
        return array(
            array(array()),
            array(array('newsletterId' => 1, 'users' => array('henrikbjorn'))),
        );
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
        $envelope = new Envelope(new DefaultMessage('SendNewsletter', array()));
        $json = $this->serializer->serialize($envelope);

        $this->assertEquals($envelope, $this->serializer->deserialize($json));
    }
}
