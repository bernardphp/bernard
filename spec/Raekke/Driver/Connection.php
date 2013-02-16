<?php

namespace spec\Raekke\Driver;

use PHPSpec2\ObjectBehavior;

class Connection extends ObjectBehavior
{
    /**
     * @param Raekke\Driver\Configuration $configuration
     * @param Predis\Client $predis
     */
    function let($configuration, $predis)
    {
        $configuration->getPrefix()->willReturn('phpspec');

        $this->beConstructedWith($predis, $configuration);
    }

    function it_creates_a_configuration_if_none_given($predis)
    {
        $this->beConstructedWith($predis, null);

        $this->getConfiguration()->shouldReturnAnInstanceOf('Raekke\Driver\Configuration');
    }

    function it_hangson_to_the_configuration_and_client_given($predis, $configuration)
    {
        $this->beConstructedWith($predis, $configuration);

        $this->getConfiguration()->shouldReturn($configuration);
        $this->getClient()->shouldReturn($predis);
    }

    function it_creates_a_client_if_given_parameters_instead_of_client()
    {
        $this->beConstructedWith('tcp://localhost');

        $this->getClient()->shouldReturnAnInstanceOf('Predis\Client');
    }
}
