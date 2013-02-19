<?php

namespace spec\Raekke;

use PHPSpec2\ObjectBehavior;
use Predis\Command\Processor\KeyPrefixProcessor;

class Connection extends ObjectBehavior
{
    /**
     * @param Predis\Client $client
     */
    function it_has_a_client($client)
    {
        $this->beConstructedWith($client);
        $this->getClient()->shouldReturn($client);
    }
}
