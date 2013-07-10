<?php

namespace Bernard\Symfony\Command;

use Bernard\Producer;
use Bernard\Message\DefaultMessage;
use Bernard\QueueFactory;
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
     * @param Producer $consumer
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
            ->addArgument('service', InputArgument::REQUIRED, 'Name of the service (i.e. job), as registered in the bernard config.')
            ->addArgument('data', InputArgument::OPTIONAL, 'JSON encoded data for the new job.')
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $service = $input->getArgument('service');
        // todo: check whether service registered`

        $data = $input->getArgument('data') ?: array();
        if ($data) {
            try {
                $data = json_decode($data, true);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Failed to parse json data");
            }
        }

        $this->producer->produce(new DefaultMessage($service, $data));
    }
}
