<?php

namespace Bernard\Symfony\Command;

use Bernard\Consumer;
use Bernard\Broker;
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
    protected $broker;

    /**
     * @param ServiceResolver $services
     * @param Broker          $broker
     */
    public function __construct(ServiceResolver $services, Broker $broker)
    {
        $this->services = $services;
        $this->broker = $broker;

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
        $queue = $this->broker->create($input->getArgument('queue'));

        $this->getConsumer()->consume($queue, $this->broker->create('failed'), $input->getOptions());
    }

    /**
     * @return Consumer
     */
    public function getConsumer()
    {
        return new Consumer($this->services);
    }
}
