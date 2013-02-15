<?php

namespace spec\Raekke\Driver;

use PHPSpec2\ObjectBehavior;

class Connection extends ObjectBehavior
{
    /**
     * @param Raekke\Driver\Configuration $configuration
     */
    function let($configuration)
    {
        $configuration->getNamespace()->willReturn('phpspec');

        $this->beConstructedWith('localhost', $configuration);
    }

    function letgo()
    {
        $redis = new \Redis;
        $redis->connect('localhost');
        $redis->flushAll();
    }

    function it_creates_a_configuration_if_none_given()
    {
        $this->beConstructedWith('localhost', null);

        $this->getConfiguration()->shouldReturnAnInstanceOf('Raekke\Driver\Configuration');
    }

    function it_hangson_to_the_configuration_given($configuration)
    {
        $this->beConstructedWith('localhost', $configuration);
        $this->getConfiguration()->shouldReturn($configuration);
    }

    function it_has_a_redis_connection()
    {
        $this->beConstructedWith('localhost');
        $this->getRedis()->shouldReturnAnInstanceOf('Redis');
    }

    function it_manipulates_sets()
    {
        $this->add('test', '1');

        $this->count('test')->shouldReturn(1);

        $this->add('test', '2', '3');
        $this->count('test')->shouldReturn(3);

        $this->remove('test', '2');
        $this->count('test')->shouldReturn(2);

        $this->all('test')->shouldReturn(array('1', '3'));

        $this->has('test', '1')->shouldReturn(true);
    }
}
