<?php

namespace Raekke\Tests\ServiceResolver;

use Raekke\ServiceResolver\ServiceResolver;

class ServiceResolverTest extends \PHPUnit_Framework_TestCase
{
    public function itImplementsServiceResolverInterface()
    {
        $this->assertInstanceOf('Raekke\ServiceResolver\ServiceResolverInterface', new ServiceResolver);
    }

    /**
     * @dataProvider dataProviderNotObjects
     */
    public function testItThrowsExceptionWhenServiceIsNotAnObject($name, $type)
    {
        $this->setExpectedException('InvalidArgumentException');
        $resolver = new ServiceResolver;
        $resolver->register($name, $type);
    }

    public function testItResolvesBasedOnMessageName()
    {
        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        $service = new \stdClass;

        $resolver = new ServiceResolver;
        $resolver->register('SendNewsletter', $service);

        $this->assertSame($service, $resolver->resolve($message));
    }

    public function testItThrowsExceptionIfServiceCannotBeFound()
    {
        $this->setExpectedException('InvalidArgumentException');

        $message = $this->getMock('Raekke\Message\MessageInterface');
        $message->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        $resolver = new ServiceResolver;
        $resolver->resolve($message);
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
}
