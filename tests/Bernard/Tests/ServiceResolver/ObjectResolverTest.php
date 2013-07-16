<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\ObjectResolver;
use Bernard\ServiceResolver\Invoker;

class ObjectResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsServiceResolver()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver', new ObjectResolver);
    }

    /**
     * @dataProvider dataProviderNotObjects
     */
    public function testItThrowsExceptionWhenServiceIsNotAnObject($name, $type)
    {
        $this->setExpectedException('InvalidArgumentException');
        $resolver = new ObjectResolver;
        $resolver->register($name, $type);
    }

    public function testItResolvesBasedOnMessageName()
    {
        $service = new \stdClass;

        $resolver = new ObjectResolver;
        $resolver->register('SendNewsletter', $service);

        $envelope = $this->createEnvelope();

        $this->assertEquals(array($service, 'onSendNewsletter'), $resolver->resolve($envelope));
    }

    public function testItThrowsExceptionIfServiceCannotBeFound()
    {
        $this->setExpectedException('InvalidArgumentException');

        $resolver = new ObjectResolver;
        $resolver->resolve($this->createEnvelope());
    }

    public function dataProviderNotObjects()
    {
        return array(
            array('SendNewsletter', 'string'),
            array('SendNewsletter', true),
            array('SendNewsletter', false),
            array('SendNewsletter', 1.02),
            array('SendNewsletter', 12),
        );
    }

    protected function createEnvelope()
    {
        $message = $this->getMock('Bernard\Message');
        $message->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        return new Envelope($message);
    }
}
