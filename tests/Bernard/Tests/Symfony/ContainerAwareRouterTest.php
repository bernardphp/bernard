<?php

namespace Bernard\Tests\Symfony;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Symfony\ContainerAwareRouter;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new Container;
        $this->container->set('my.service', function () {
            return 'var_dump';
        });

        $this->router = new ContainerAwareRouter($this->container);
    }

    public function testUndefinedServicesAreNotAccepted()
    {
        $this->setExpectedException('Bernard\Exception\ReceiverNotFoundException');

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new ContainerAwareRouter($this->container, array('SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }

    public function testAcceptsContainerServiceAsReceiver()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->router->add('SendNewsletter', 'my.service');
        $this->assertSame($this->container->get('my.service'), $this->router->map($envelope));
    }
}
