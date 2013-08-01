<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Message\Envelope;
use Bernard\ServiceResolver\ObjectResolver;

class ObjectResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testImplementsServiceResolver()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver', new ObjectResolver);
    }

    public function testArrayOfServicesInConstructor()
    {
        $resolver = new ObjectResolver(array(
            'SendNewsletter' => 'Bernard\Tests\Fixtures\Service',
        ));

        $envelope = $this->createEnvelope();
        $this->assertEquals(array('Bernard\Tests\Fixtures\Service', 'onSendNewsletter'), $resolver->resolve($envelope));
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

    public function testItAllowsClassNamesForStaticAccess()
    {
        $resolver = new ObjectResolver;
        $resolver->register('SendNewsletter', 'Bernard\Tests\Fixtures\Service');

        $envelope = $this->createEnvelope();
        $callable = array('Bernard\Tests\Fixtures\Service', 'onSendNewsletter');

        $this->assertEquals($callable, $resolver->resolve($envelope));
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
