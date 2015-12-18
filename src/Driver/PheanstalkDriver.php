<?php

namespace Bernard\Driver;

use Pheanstalk\PheanstalkInterface;

/**
 * Implements a Driver for use with https://github.com/pda/pheanstalk
 *
 * @package Bernard
 */
class PheanstalkDriver implements \Bernard\Driver
{
    protected $pheanstalk;

    /**
     * @param PheanstalkInterface $pheanstalk
     */
    public function __construct(PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return $this->pheanstalk->listTubes();
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        $stats = $this->pheanstalk->statsTube($queueName);

        return $stats['current-jobs-ready'];
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->pheanstalk->putInTube($queueName, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        if ($job = $this->pheanstalk->reserveFromTube($queueName, $duration)) {
            return [$job->getData(), $job];
        }

        return array(null, null);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->pheanstalk->delete($receipt);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return $this->pheanstalk
            ->stats()
            ->getArrayCopy()
        ;
    }
}
