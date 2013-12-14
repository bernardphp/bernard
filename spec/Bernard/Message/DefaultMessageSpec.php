<?php

namespace spec\Bernard\Message;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DefaultMessageSpec extends ObjectBehavior
{
    function its_a_message()
    {
        $this->beConstructedWith('Import');

        $this->shouldHaveType('Bernard\Message');
    }

    function it_have_arbitary_properties()
    {
        $this->beConstructedWith('Import', array(
            'newsletterId' => 10,
            'members' => array('Henrik', 'Antoine'),
        ));

        $this->shouldHavePropertyValue('newsletterId', 10);
        $this->shouldHavePropertyValue('members', array('Henrik', 'Antoine'));
    }

    function it_has_a_name()
    {
        $this->beConstructedWith('Import');

        $this->getName()->shouldReturn('Import');
    }

    function it_normalizes_the_name()
    {
        $this->beConstructedWith('This Is Not Normalized');

        $this->getName()->shouldReturn('ThisIsNotNormalized');
    }

    public function getMatchers()
    {
        return array(
            'havePropertyValue' => function ($subject, $key, $value) {
                return property_exists($subject, $key) && $subject->$key == $value;
            },
        );
    }
}
