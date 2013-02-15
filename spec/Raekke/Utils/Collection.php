<?php

namespace spec\Raekke\Utils;

use PHPSpec2\ObjectBehavior;

class Collection extends ObjectBehavior
{
    function it_should_behave_like_an_array()
    {
        $this->shouldHaveType('Countable');
        $this->shouldHaveType('IteratorAggregate');
    }

    function it_returns_default_value_if_key_isnt_set()
    {
        $this->get('key0', 'default0')->shouldReturn('default0');
    }

    function it_should_allow_to_recieve_remove_and_get_elements()
    {
        $this->has('key0')->shouldReturn(false);
        $this->get('key0')->shouldReturn(null);

        $this->set('key0', 'value0');

        $this->has('key0')->shouldReturn(true);
        $this->get('key0')->shouldReturn('value0');

        $this->remove('key0');

        $this->has('key0')->shouldReturn(false);
        $this->get('key0')->shouldReturn(null);
    }

    /**
     * @param ArrayIterator $iterator
     */
    function it_should_allow_to_get_all_values_at_once($iterator)
    {
        $this->all()->shouldReturnAnInstanceOf('ArrayIterator');

        $this->set('key0', 'value0');

        $iterator = $this->all();
        $iterator->count()->shouldReturn(1);

        $iterator = $this->getIterator();
        $iterator->count()->shouldReturn(1);
    }
}
