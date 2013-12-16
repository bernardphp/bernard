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
    protected $shutdown = false;

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
        $options = $input->getOptions();
        $options['max-runtime'] += time();

        declare(ticks = 10) {
            $this->bind($output);

            while ($this->tick($queue, $input->getOptions())) {
                // http://php.net/pcntl_signal_dispatch says that this MUST be called in each loop
                // if using php to run long running Daemon scripts.
                pcntl_signal_dispatch();
            }
        }
    }

    protected function tick($queue, $options)
    {
        if ($options['max-runtime'] >= time()) {
            return false;
        }

        if ($this->shutdown) {
            return false;
        }

        return $this->consumer->consume($queue, $options);
    }

    protected function bind(OutputInterface $output)
    {
        $callback = function ($signal) use ($output) {
            $this->shutdown = true;

            $output->writeln('Caught signal "' . $signal . '". Terminating...');
        };

        pcntl_signal(SIGINT, $callback);
    }
}
