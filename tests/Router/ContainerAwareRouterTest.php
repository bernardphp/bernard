<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Router\ContainerAwareRouter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ContainerAwareRouterTest extends TestCase
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setUp()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get('my.service')->willReturn(
            function () {
                return 'var_dump';
            }
        );
        $container->has('my.service')->willReturn(true);

        $this->container = $container->reveal();
    }

    public function testAcceptsInConstructor()
    {
        $router = new ContainerAwareRouter(
            $this->container,
            ['SendNewsletter' => 'my.service']
        );
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->assertSame(
            $this->container->get('my.service'),
            $router->map($envelope)
        );
    }

    public function testAcceptsContainerServiceAsReceiver()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $router = new ContainerAwareRouter(
            $this->container,
            ['SendNewsletter' => 'my.service']
        );

        $this->assertSame(
            $this->container->get('my.service'),
            $router->map($envelope)
        );
    }
}
