<?php

namespace Bernard\Command;

use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Bernard
 */
class ProduceCommand extends \Symfony\Component\Console\Command\Command
{
    protected $producer;

    /**
     * @param Producer $producer
     */
    public function __construct(Producer $producer)
    {
        $this->producer = $producer;

        parent::__construct('bernard:produce');
    }

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'Name of a queue to add this job to. By default the queue is guessed from the message name.', null)
            ->addArgument('name', InputArgument::REQUIRED, 'Name for the message eg. "ImportUsers".')
            ->addArgument('message', InputArgument::OPTIONAL, 'JSON encoded string that is used for message properties.')
            ->addArgument('queueName', InputArgument::OPTIONAL, 'Name of the queue to put the message in.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name      = $input->getArgument('name');
        $message   = json_decode($input->getArgument('message'), true) ?: array();
        $queueName = $input->getArgument('queueName');

        if (json_last_error()) {
            throw new \RuntimeException('Could not decode invalid JSON [' . json_last_error() . ']');
        }

        $this->producer->produce(new DefaultMessage($name, $message), $queueName);
    }
}
