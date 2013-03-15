<?php

namespace Bernard\Tests\Serializer;

use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Serializer\JMSSerializer;

class JMSSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = new JMSSerializer($this->createJMSSerializer());
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
        $class = new \ReflectionClass('Bernard\Serializer');
        $builder = new \JMS\Serializer\SerializerBuilder();
        $builder->addMetadataDir(dirname($class->getFilename()) . '/Resources/serializer', 'Bernard');
        $builder->configureListeners(function ($dispatcher) {
//            $dispatcher->addSubscriber(new \Bernard\Serializer\EventListener\EnvelopeListener());
        });

        return $builder->build();
    }
}

namespace Application;
class SendNewsletterMessage extends \Bernard\Message\AbstractMessage {}
