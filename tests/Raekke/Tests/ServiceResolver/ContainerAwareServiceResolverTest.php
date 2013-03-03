<?php

namespace Raekke\Tests\ServiceResolver;

use Raekke\Message\DefaultMessage;
use Raekke\ServiceResolver\ContainerAwareServiceResolver;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareServiceResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function createResolver()
    {
        $this->container = new Container;

        return new ContainerAwareServiceResolver($this->container);
    }

    public function testExceptionWhenMessageCannotBeResolved()
    {
        $this->setExpectedException('InvalidArgumentException',
            'No service registered for message "SendNewsletter".'); 

        $resolver = $this->createResolver();

        $this->assertEquals(array('service_container'), $this->container->getServiceIds());

        $resolver->resolve(new DefaultMessage('SendNewsletter'));
    }

    public function testResolveToServiceFromContainer()
    {
        $resolver = $this->createResolver();

        $this->container->set('my.service.id', $service = new \stdClass);

        $resolver->register('SendNewsletter', 'my.service.id');

        $this->assertSame($service, $resolver->resolve(new DefaultMessage('SendNewsletter')));
    }

    public function testExceptionWhenServiceDosentExistOnContainer()
    {
        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');

        $resolver = $this->createResolver();
        $resolver->register('SendNewsletter', 'non_existant_service_id');
        $resolver->resolve(new DefaultMessage('SendNewsletter'));
    }

}
