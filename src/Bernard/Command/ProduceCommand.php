<?php

namespace Bernard\Command;

use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Symfony\Component\Console\Input\InputArgument;
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
            ->addArgument('name', InputArgument::REQUIRED, 'Name for the message eg. "ImportUsers".')
            ->addArgument('message', InputArgument::OPTIONAL, 'JSON encoded string that is used for message properties.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $name    = $input->getArgument('name');
        $message = json_decode($input->getArgument('message'), true) ?: array();

        if (json_last_error()) {
            throw new \RuntimeException('Could not decode invalid JSON [' . json_last_error() . ']');
        }

        $this->producer->produce(new DefaultMessage($name, $message));
    }
}
