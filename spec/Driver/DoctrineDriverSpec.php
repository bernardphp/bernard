<?php

namespace spec\Bernard\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use PhpSpec\ObjectBehavior;
use PhpSpec\Exception\Example\SkippingException;
use Prophecy\Argument;

class DoctrineDriverSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        if (defined('HHVM_VERSION')) {
            throw new SkippingException('Doctrine have incompatibility issues with HHVM.');
        }

        $this->beConstructedWith($connection);
    }
    function it_is_initializable()
    {
        $this->shouldHaveType('Bernard\Driver\DoctrineDriver');
    }

    function it_is_a_driver()
    {
        $this->shouldImplement('Bernard\Driver');
    }

    function it_lists_queues(Connection $connection, Statement $statement)
    {
        $connection->prepare(Argument::type('string'))->willReturn($statement);
        $statement->execute()->shouldBeCalled();
        $statement->fetchAll(\PDO::FETCH_COLUMN)->willReturn(array('send-newsletter'));

        $this->listQueues()->shouldReturn(array('send-newsletter'));
    }

    function it_creates_a_queue(Connection $connection)
    {
        $connection->insert('bernard_queues', array('name' => 'send-newsletter'))->shouldBeCalled();

        $this->createQueue('send-newsletter');
    }

    function it_creates_a_queue_and_catches_an_exception(Connection $connection)
    {
        $connection->insert('bernard_queues', array('name' => 'send-newsletter'))->willThrow('Exception');

        $this->shouldNotThrow('Exception')->duringCreateQueue('send-newsletter');
    }

    function it_counts_messages(Connection $connection)
    {
        $connection->fetchColumn(Argument::type('string'), array('queue' => 'send-newsletter'))->willReturn(5);

        $this->countMessages('send-newsletter')->shouldReturn(5);
    }

    function it_pushes_a_message_to_the_queue(Connection $connection)
    {
        $types = array('string', 'string', 'datetime');
        $data = array(
            'queue'   => 'send-newsletter',
            'message' => 'message',
            'sentAt'  => new \DateTime,
        );

        $connection->insert('bernard_messages', $data, $types)->shouldBeCalled();

        $this->pushMessage('send-newsletter', 'message');
    }
}
