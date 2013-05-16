<?php

namespace Bernard\Symfony\Command;

use Bernard\Consumer;
use Bernard\QueueFactory;
use Bernard\ServiceResolver;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Bernard
 */
class ConsumeCommand extends \Symfony\Component\Console\Command\Command
{
    protected $services;
    protected $queues;

    /**
     * @param ServiceResolver       $services
     * @param QueueFactoryInterface $queues
     */
    public function __construct(ServiceResolver $services, QueueFactory $queues)
    {
        $this->services = $services;
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
            ->addArgument('queue', InputArgument::REQUIRED, 'Name of queue that will be consumed.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->queues->create($input->getArgument('queue'));

        $this->getConsumer()->consume($queue, $this->queues->create('failed'), $input->getOptions());
    }

    /**
     * @return Consumer
     */
    public function getConsumer()
    {
        return new Consumer($this->services);
    }
}
