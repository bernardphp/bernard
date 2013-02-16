<?php

namespace spec\Raekke\Driver;

use PHPSpec2\ObjectBehavior;

class Configuration extends ObjectBehavior
{
    function it_returns_a_namespace()
    {
        $this->getPrefix()->shouldReturn('raekke');

        $this->setPrefix('custom-prefix');
        $this->getPrefix()->shouldReturn('custom-prefix');
    }
}
