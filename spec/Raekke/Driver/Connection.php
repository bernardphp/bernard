<?php

namespace spec\Raekke\Driver;

use PHPSpec2\ObjectBehavior;
use Predis\Command\Processor\KeyPrefixProcessor;

class Connection extends ObjectBehavior
{
    /**
     * @param Raekke\Driver\Configuration $configuration
     */
    function let($configuration)
    {
        $configuration->getPrefix()->willReturn('phpspec');

        $this->beConstructedWith('tcp://localhost', $configuration);
    }

    function it_have_a_configuration($configuration)
    {
        $this->beConstructedWith('tcp://localhost', $configuration);

        $this->getConfiguration()->shouldReturn($configuration);
    }

    function it_creates_a_client($configuration)
    {
        $this->beConstructedWith('tcp://localhost', $configuration);

        $this->getClient()->shouldReturnAnInstanceOf('Predis\Client');
    }
}
