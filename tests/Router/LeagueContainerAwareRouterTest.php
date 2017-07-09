<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Router\LeagueContainerAwareRouter;
use League\Container\Container;

class LeagueContainerAwareRouterTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->container = new Container;
        $this->container->add('my.service', function () {
            return 'var_dump';
        });
    }

    /**
     * @expectedException \League\Container\Exception\NotFoundException
     */
    public function testUndefinedServicesAreNotAccepted()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $router = new LeagueContainerAwareRouter($this->container);
        $router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new LeagueContainerAwareRouter($this->container, array('SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }
}
