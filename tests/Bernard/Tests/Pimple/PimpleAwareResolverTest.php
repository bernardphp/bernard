<?php

namespace Bernard\Tests\Pimple;

use Bernard\ServiceResolver\Invocator;
use Bernard\Message\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Pimple\PimpleAwareResolver;
use Pimple;

class PimpleAwareResolverTest extends \PHPUnit_Framework_TestCase
{
    protected function createResolver()
    {
        $this->container = new \Pimple;

        return new PimpleAwareResolver($this->container);
    }

    public function testExceptionWhenMessageCannotBeResolved()
    {
        $this->setExpectedException('InvalidArgumentException',
            'No service registered for envelope "SendNewsletter".'); 

        $resolver = $this->createResolver();

        $this->assertEquals(array(), $this->container->keys());

        $resolver->resolve($this->createEnvelope());
    }

    public function testResolveToServiceFromContainer()
    {
        $resolver = $this->createResolver();

        $this->container['my.service.id'] = $service = new \stdClass;

        $resolver->register('SendNewsletter', 'my.service.id');

        $this->assertEquals(new Invocator($service, $this->createEnvelope()), $resolver->resolve($this->createEnvelope()));
    }

    public function testExceptionWhenServiceDosentExistOnContainer()
    {
        $this->setExpectedException('InvalidArgumentException',
            'Identifier "non_existant_service_id" is not defined.'); 

        $resolver = $this->createResolver();
        $resolver->register('SendNewsletter', 'non_existant_service_id');
        $resolver->resolve($this->createEnvelope());
    }

    protected function createEnvelope()
    {
        return new Envelope(new DefaultMessage('SendNewsletter'));
    }

}
