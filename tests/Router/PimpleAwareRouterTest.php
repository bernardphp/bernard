<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\DefaultMessage;
use Bernard\Router\PimpleAwareRouter;
use Pimple;

class PimpleAwareRouterTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->pimple = new Pimple;
        $this->pimple['my.service'] = $this->pimple->share(function () {
            return 'var_dump';
        });

        $this->router = new PimpleAwareRouter($this->pimple);
    }

    public function testUndefinedServicesAreNotAccepted()
    {
        $this->setExpectedException('InvalidArgumentException');

        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new PimpleAwareRouter($this->pimple, array('SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->assertSame($this->pimple['my.service'], $router->map($envelope));
    }

    public function testAcceptsPimpleServiceAsReceiver()
    {
        $envelope = new Envelope(new DefaultMessage('SendNewsletter'));

        $this->router->add('SendNewsletter', 'my.service');

        $this->assertSame($this->pimple['my.service'], $this->router->map($envelope));
    }
}
