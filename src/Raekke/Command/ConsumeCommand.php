<?php

namespace Raekke\Command;

use Raekke\Consumer;
use Raekke\QueueFactory;
use Raekke\ServiceResolverInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * @package Raekke
 */
class ConsumeCommand extends \Symfony\Component\Console\Command\Command
{
    protected $services;
    protected $queues;

    public function __construct(
        ServiceResolverInterface $services,
        QueueFactory $queues
    ) {
        $this->services = $services;
        $this->queues = $queues;

        parent::__construct();
    }

    public function configure()
    {
        $this
            ->setName('raekke:consume')
            ->addOption('max-retries', null, InputOption::VALUE_OPTIONAL, 'Number of times a message will be requeued before marked as failed.', null)
            ->addArgument('queue', InputArgument::INPUT_REQUIRED, 'Name of queue that will be consumed.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getConsumer()->consume($this->queues->create($input->getArgument('queue')), array(
            'max_retries' => $input->getOption('max-retries'),
        ));
    }

    public function getConsumer()
    {
        return new Consumer($this->services, $this->queues->create('failed'));
    }
}
