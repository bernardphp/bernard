<?php

namespace Raekke\Tests\Serializer;

use Raekke\Message\MessageWrapper;
use Raekke\Message\DefaultMessage;
use Raekke\Serializer\Serializer;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = new Serializer($this->createJMSSerializer());
    }

    public function testItSerializesDefaultMessage()
    {
        $json = '{"message":{"name":"SendNewsletter"},"name":"SendNewsletter","class":"Raekke\\\\Message\\\\DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter')));

        $json = '{"message":{"name":"SendNewsletter","newsletterId":1,"users":["henrikbjorn"]},"name":"SendNewsletter","class":"Raekke\\\\Message\\\\DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter', array(
            'newsletterId' => 1,
            'users' => array(
                'henrikbjorn'
            ),
        ))));
    }

    public function testItSerializesACustomImplementedMessage()
    {
        $this->markTestIncomplete('Custom messages are not yet implemented.');

        $json = '{"message":[],"name":"SendNewsletter","class":"Application\\\\SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new MessageWrapper(new \Application\SendNewsletterMessage())));
    }

    public function testItDeserializesACustomImplementedMessage()
    {
        $this->markTestIncomplete('Custom messages are not yet implemented.');

        $json = '{"message":[],"name":"SendNewsletter","class":"Application\\\\SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new MessageWrapper(new \Application\SendNewsletterMessage())));
    }

    public function testItDeserializesDefaultMessage()
    {
        $message = $this->createWrappedDefaultMessage('SendNewsletter');
        $json = $this->serializer->serialize($message);

        $this->assertEquals($message, $this->serializer->deserialize($json));
    }

    public function createWrappedDefaultMessage($name, array $properties = array())
    {
        return new MessageWrapper(new DefaultMessage($name, $properties));
    }

    public function createJMSSerializer()
    {
        $class = new \ReflectionClass('Raekke\Serializer\Serializer');
        $builder = new \JMS\Serializer\SerializerBuilder();
        $builder->addMetadataDir(dirname($class->getFilename()) . '/../Resources/serializer', 'Raekke');

        return $builder->build();
    }
}

namespace Application;
class SendNewsletterMessage extends \Raekke\Message\Message {}
