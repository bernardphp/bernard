<?php

namespace Bernard\Tests\ServiceResolver;

use Bernard\Message\Envelope;
use Bernard\Symfony\ContainerAwareResolver;
use Bernard\ServiceResolver\Invoker;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function createResolver()
    {
        $this->container = new Container;

        return new ContainerAwareResolver($this->container);
    }

    public function testExceptionWhenMessageCannotBeResolved()
    {
        $this->setExpectedException('InvalidArgumentException', 'No service registered for envelope "SendNewsletter".');

        $resolver = $this->createResolver();
        $envelope = $this->createEnvelope();

        $this->assertEquals(array('service_container'), $this->container->getServiceIds());

        $resolver->resolve($envelope);
    }

    public function testResolveToServiceFromContainer()
    {
        $resolver = $this->createResolver();
        $envelope = $this->createEnvelope();

        $this->container->set('my.service.id', $service = new \stdClass);

        $resolver->register('SendNewsletter', 'my.service.id');

        $this->assertEquals(new Invoker($service, $envelope), $resolver->resolve($envelope));
    }

    public function testExceptionWhenServiceDosentExistOnContainer()
    {
        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');

        $resolver = $this->createResolver();
        $resolver->register('SendNewsletter', 'non_existant_service_id');
        $resolver->resolve($this->createEnvelope());
    }

    protected function createEnvelope()
    {
        $message = $this->getMock('Bernard\Message');
        $message->expects($this->any())->method('getName')->will($this->returnValue('SendNewsletter'));

        return new Envelope($message);
    }
}
