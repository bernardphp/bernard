<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\LeagueContainerAwareRouter;
use League\Container\Container;

class LeagueContainerAwareRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->container = new Container;
        $this->container->add('my.service', function () {
            return 'var_dump';
        });
    }

    public function testUndefinedServicesAreNotAccepted()
    {
        $this->setExpectedException('League\Container\Exception\ReflectionException');

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $router = new LeagueContainerAwareRouter($this->container);
        $router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new LeagueContainerAwareRouter($this->container, array('SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->assertSame($this->container->get('my.service'), $router->map($envelope));
    }
}
