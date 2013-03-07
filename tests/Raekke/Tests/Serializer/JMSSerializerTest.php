<?php

namespace Raekke\Tests\Serializer;

use Raekke\Message\Envelope;
use Raekke\Message\DefaultMessage;
use Raekke\Serializer\JMSSerializer;

class JMSSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = new JMSSerializer($this->createJMSSerializer());
    }

    public function testImplementsSerializer()
    {
        $this->assertInstanceOf('Raekke\Serializer', $this->serializer);
    }

    public function testItSerializesDefaultMessage()
    {
        $json = '{"args":{"name":"SendNewsletter"},"class":"Raekke:Message:DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter')));

        $json = '{"args":{"name":"SendNewsletter","newsletterId":1,"users":["henrikbjorn"]},"class":"Raekke:Message:DefaultMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize($this->createWrappedDefaultMessage('SendNewsletter', array(
            'newsletterId' => 1,
            'users' => array(
                'henrikbjorn'
            ),
        ))));
    }

    public function testItSerializesACustomImplementedMessage()
    {
        $json = '{"args":{},"class":"Application:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new \Application\SendNewsletterMessage())));
    }

    public function testItDeserializesACustomImplementedMessage()
    {
        $json = '{"args":{},"class":"Application:SendNewsletterMessage","timestamp":' . time() . ',"retries":0}';
        $this->assertEquals($json, $this->serializer->serialize(new Envelope(new \Application\SendNewsletterMessage())));
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

    public function createJMSSerializer()
    {
        $class = new \ReflectionClass('Raekke\Serializer');
        $builder = new \JMS\Serializer\SerializerBuilder();
        $builder->addMetadataDir(dirname($class->getFilename()) . '/Resources/serializer', 'Raekke');
        $builder->configureListeners(function ($dispatcher) {
//            $dispatcher->addSubscriber(new \Raekke\Serializer\EventListener\EnvelopeListener());
        });

        return $builder->build();
    }
}

namespace Application;
class SendNewsletterMessage extends \Raekke\Message\AbstractMessage {}
