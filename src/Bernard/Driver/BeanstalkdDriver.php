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
    protected $beanstalkd;

    /**
     * Constructor.
     *
     * @param Pheanstalk_PheanstalkInterface $beanstalkd
     */
    public function __construct(\Pheanstalk_PheanstalkInterface $beanstalkd)
    {
        $this->beanstalkd = $beanstalkd;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        return (integer) $this->beanstalkd->statsTube($queueName)->total_jobs;
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $this->beanstalkd->useTube($queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        do {
            try {
                $this->beanstalkd->delete($this->beanstalkd->peekReady($queueName));
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
        return $this->beanstalkd->listTubes();
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->beanstalkd->putInTube($queueName, $message);
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        try {
            $job = $this->beanstalkd->peekReady($queueName);
        } catch (\Exception $e) {
            return array(null, null);
        }

        return array($job->getData(), $job->getId());
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
        $this->beanstalkd->delete($this->beanstalkd->peek($receipt));
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
    }
}
