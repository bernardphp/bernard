<?php

namespace spec\Raekke\Message;

use PHPSpec2\ObjectBehavior;
use PHPSpec2\Matcher\InlineMatcher;
use PHPSpec2\Matcher\CustomMatchersProviderInterface;

class DefaultMessage extends ObjectBehavior implements CustomMatchersProviderInterface
{
    function it_extends_the_abstract_message()
    {
        $this->beConstructedWith('MyMessageName');
        $this->shouldHaveType('Raekke\Message\Message');
    }

    function it_has_a_name()
    {
        $this->beConstructedWith('MyMessageName');

        $this->getName()->shouldReturn('MyMessageName');
    }

    function it_uses_normalized_name_for_queue()
    {
        $this->beConstructedWith('MyMessageName');

        $this->getQueue()->shouldReturn('my-message-name');
    }

    function it_supports_a_dynamic_number_of_properties()
    {
        $this->beConstructedWith('MyMessageName', array(
            'something' => 'somethingvalue',
            'something2' => 'somethingvalue2',
            'messageName' => 'somethingelse',
        ));

        $this->shouldHaveAttributeWithValue('something', 'somethingvalue');
        $this->shouldHaveAttributeWithValue('something2', 'somethingvalue2');

        $this->getName()->shouldReturn('MyMessageName');
    }

    public static function getMatchers()
    {
        return array(
            new InlineMatcher('haveAttributeWithValue', function ($subject, $property, $value) {
                return property_exists($subject, $property) && $subject->$property === $value;
            }),
        );
    }
}
