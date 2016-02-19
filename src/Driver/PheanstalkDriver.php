<?php

namespace Bernard\Driver;

use Pheanstalk\PheanstalkInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Implements a Driver for use with https://github.com/pda/pheanstalk
 *
 * @package Bernard
 */
class PheanstalkDriver extends AbstractDriver
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
    public function createQueue($queueName, array $options = [])
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
    public function pushMessage($queueName, $message, array $options = [])
    {
        $options = $this->validatePushOptions($options);

        $this->pheanstalk->putInTube(
            $queueName,
            $message,
            $options['priority'],
            $options['delay'],
            $options['ttr']
        );
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

    /**
     * {@inheritdoc}
     */
    public function configurePushOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'priority' => PheanstalkInterface::DEFAULT_PRIORITY,
            'delay' => PheanstalkInterface::DEFAULT_DELAY,
            'ttr' => PheanstalkInterface::DEFAULT_TTR,
        ));
    }
}
