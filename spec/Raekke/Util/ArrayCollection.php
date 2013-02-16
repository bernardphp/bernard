<?php

namespace spec\Raekke\Util;

use PHPSpec2\ObjectBehavior;

class ArrayCollection extends ObjectBehavior
{
    function it_returns_default_when_key_dosent_exists()
    {
        $this->containsKey('key')->shouldReturn(false);
        $this->get('key', 'defaultValue')->shouldReturn('defaultValue');
    }
}
