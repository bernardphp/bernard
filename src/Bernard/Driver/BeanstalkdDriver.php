<?php

namespace Bernard\Driver;

/**
 * Implements a Driver for use with:
 * @see http://kr.github.io/beanstalkd/
 * @see https://github.com/pda/pheanstalk
 *
 * @package Bernard
 */
class BeanstalkdDriver implements \Bernard\Driver
{
    /**
     * @var \Pheanstalk_PheanstalkInterface
     */
    protected $pheanstalk;

    /**
     * Constructor.
     *
     * @param Pheanstalk_PheanstalkInterface $pheanstalk
     */
    public function __construct(\Pheanstalk_PheanstalkInterface $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return (integer) $this->pheanstalk->statsTube($queueName)->total_jobs;
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $this->pheanstalk->useTube($queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        do {
            try {
                $this->pheanstalk->delete($this->pheanstalk->peekReady($queueName));
            } catch (\Pheanstalk_Exception_ServerException $e) {
                // the queue is now empty and Beanstalkd will
                // remove the queue automatically.
                return;
            }
        } while (true);
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        return $this->pheanstalk->listTubes();
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->pheanstalk->putInTube($queueName, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        $runtime = microtime(true) + $interval;

        while (microtime(true) < $runtime) {
            try {
                if (!$job = $this->pheanstalk->peekReady($queueName)) {
                    usleep(10000);
                    continue;
                }

                return array($job->getData(), $job->getId());
            } catch (\Exception $e) {
                return array(null, null);
            }
        }

        return array(null, null);
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->pheanstalk->delete($this->pheanstalk->peek($receipt));
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
    }
}
