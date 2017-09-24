<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Router\SymfonyContainerAwareRouter;
use Symfony\Component\DependencyInjection\Container;

class SymfonyContainerAwareRouterTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->container = new Container;
        $this->container->set('my.service', function () {
            return 'var_dump';
        });
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testUndefinedServicesAreNotAccepted()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $router = new SymfonyContainerAwareRouter($this->container);
        $router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new SymfonyContainerAwareRouter($this->container, array( 'SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }

    public function testAcceptsContainerServiceAsReceiver()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $router = new SymfonyContainerAwareRouter($this->container, array(
            'SendNewsletter' => 'my.service',
        ));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }
}
