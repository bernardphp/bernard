<?php

namespace Bernard\Symfony\Command;

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

        if (!json_last_error()) {
            return $this->producer->produce(new DefaultMessage($name, $message));
        }

        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                $error = 'Unknown error';
                break;
        }

        throw new \RuntimeException('"'. $error .'" occured when decoding JSON data.');
    }
}
