<?php

namespace spec\Raekke;

use PHPSpec2\ObjectBehavior;

class Configuration extends ObjectBehavior
{
    function it_returns_a_namespace()
    {
        $this->getNamespace()->shouldReturn('raekke');

        $this->setNamespace('new-namespace');
        $this->getNamespace()->shouldReturn('new-namespace');
    }
}
