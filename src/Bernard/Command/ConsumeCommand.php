<?php

namespace Bernard\Command;

use Bernard\Consumer;
use Bernard\QueueFactory;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Bernard
 */
class ConsumeCommand extends \Symfony\Component\Console\Command\Command
{
    protected $consumer;
    protected $queues;

    /**
     * @param Consumer     $consumer
     * @param QueueFactory $queues
     */
    public function __construct(Consumer $consumer, QueueFactory $queues)
    {
        $this->consumer = $consumer;
        $this->queues = $queues;

        parent::__construct('bernard:consume');
    }

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addArgument('queue', InputArgument::REQUIRED, 'Name of queue that will be consumed.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->queues->create($input->getArgument('queue'));

        $this->consumer->consume($queue, $input->getOptions());
    }
}
