<?php

namespace Bernard\Tests\Router;

use Bernard\Envelope;
use Bernard\Message\PlainMessage;
use Bernard\Router\PimpleAwareRouter;
use Pimple;

class PimpleAwareRouterTest extends \PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->pimple = new Pimple;
        $this->pimple['my.service'] = $this->pimple->share(function () {
            return 'var_dump';
        });

        $this->router = new PimpleAwareRouter($this->pimple);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUndefinedServicesAreNotAccepted()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->router->map($envelope);
    }

    public function testAcceptsInConstructor()
    {
        $router = new PimpleAwareRouter($this->pimple, array('SendNewsletter' => 'my.service'));
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->assertSame($this->pimple['my.service'], $router->map($envelope));
    }

    public function testAcceptsPimpleServiceAsReceiver()
    {
        $envelope = new Envelope(new PlainMessage('SendNewsletter'));

        $this->router->add('SendNewsletter', 'my.service');

        $this->assertSame($this->pimple['my.service'], $this->router->map($envelope));
    }
}
