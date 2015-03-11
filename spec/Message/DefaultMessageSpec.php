<?php

namespace spec\Bernard\Message;

use PhpSpec\ObjectBehavior;

class DefaultMessageSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('Import');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Message\DefaultMessage');
    }

    function it_is_a_message()
    {
        $this->shouldImplement('Bernard\Message');
    }

    function it_has_a_name()
    {
        $this->beConstructedWith('Import');

        $this->getName()->shouldReturn('Import');
    }

    function it_can_have_arguments()
    {
        $arguments = array(
            'newsletterId' => 10,
            'members' => array('Henrik', 'Antoine'),
        );

        $this->beConstructedWith('Import', $arguments);

        $this->all()->shouldReturn($arguments);
        $this->get('newsletterId')->shouldReturn(10);
        $this->__get('newsletterId')->shouldReturn(10);
        $this->has('newsletterId')->shouldReturn(true);
    }

    function it_throws_an_exception_when_trying_to_modify_a_property()
    {
        $this->shouldThrow('LogicException')->duringOffsetSet('property', 'value');
        $this->shouldThrow('LogicException')->during__set('property', 'value');
        $this->shouldThrow('LogicException')->duringOffsetUnset('property');
    }
}
