<?php

namespace Bernard\Symfony\Command;

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

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this
            ->setName('bernard:consume')
            ->addOption('max-retries', null, InputOption::VALUE_OPTIONAL, 'Number of times a message will be requeued before marked as failed.', null)
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addOption('failed', null, InputOption::VALUE_OPTIONAL, 'Messages failed more than {max-retries} will be queued here.', null)
            ->addArgument('queue', InputArgument::REQUIRED, 'Name of queue that will be consumed.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOptions();
        $queue = $this->queues->create($input->getArgument('queue'));
        $failed = isset($options['failed']) ? $this->queues->create($options['failed']) : null;

        unset($options['failed']);

        $this->consumer->consume($queue, $failed, $options);
    }
}
