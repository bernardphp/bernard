<?php

namespace Raekke\Tests;

use Raekke\Message\DefaultMessage;
use Raekke\Serializer\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = new Serializer;
    }

    public function testItSerializesToJson()
    {
        $message = new DefaultMessage('SendNewsletter');
        $serialized = serialize($message);

        $this->assertEquals(json_encode(array(
            'class' => 'Raekke\Message\DefaultMessage',
            'data' => $serialized,
            'timestamp' => time(),
        )), $this->serializer->serialize($message));
    }

    public function testItSerializes()
    {
        $message = new DefaultMessage('SendNewsletter', array(
            'newsletterId' => 10,
        ));

        $json = $this->serializer->serialize($message);

        $this->assertEquals($message, $this->serializer->deserialize($json));

        $this->assertEquals(array(
            'class' => get_class($message),
            'message' => $message,
            'timestamp' => time(),
        ), $this->serializer->deserialize($json, false));
    }
}
