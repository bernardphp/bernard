<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\ContainerAwareRouter;
use Symfony\Component\DependencyInjection\Container;

class ContainerAwareRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new Container;
        $this->container->set('my.service', function () {
            return 'var_dump';
        });
    }

    public function testUndefinedServicesAreNotAccepted()
    {
        $this->setExpectedException('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $router = new ContainerAwareRouter($this->container);
        $router->map($envelope);
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

        $router = new ContainerAwareRouter($this->container, array(
            'SendNewsletter' => 'my.service',
        ));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }
}
