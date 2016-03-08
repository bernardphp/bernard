<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\ContainerInteropAwareRouter;
use Interop\Container\ContainerInterface;

class ContainerInteropAwareRouterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function setUp()
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->has('my.service')->willReturn(true);
        $container->get('my.service')->willReturn($this->sendNewsLetterHandler());

        $this->container = $container->reveal();
    }

    public function testAcceptsContainerServiceAsReceiver()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));
        $router = new ContainerInteropAwareRouter($this->container, array(
            'SendNewsletter' => 'my.service',
        ));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }

    private function sendNewsLetterHandler()
    {
        return 'var_dump';
    }
}
