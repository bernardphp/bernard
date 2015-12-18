<?php

namespace Bernard\Command;

use Bernard\Consumer;
use Bernard\Queue;
use Bernard\Queue\RoundRobinQueue;
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
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->addOption('max-runtime', null, InputOption::VALUE_OPTIONAL, 'Maximum time in seconds the consumer will run.', null)
            ->addOption('max-messages', null, InputOption::VALUE_OPTIONAL, 'Maximum number of messages that should be consumed.', null)
            ->addOption('stop-when-empty', null, InputOption::VALUE_NONE, 'Stop consumer when queue is empty.', null)
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop consumer when an error occurs.', null)
            ->addArgument('queue', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Names of one or more queues that will be consumed.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $queue = $this->getQueue($input->getArgument('queue'));

        $this->consumer->consume($queue, $input->getOptions());
    }

    /**
     * @param array|string $queue
     *
     * @return Queue
     */
    protected function getQueue($queue)
    {
        if (count($queue) > 1) {
            $queues = array_map([$this->queues, 'create'], $queue);

            return new RoundRobinQueue($queues);
        }

        if (is_array($queue)) {
            $queue = $queue[0];
        }

        return $this->queues->create($queue);
    }
}
