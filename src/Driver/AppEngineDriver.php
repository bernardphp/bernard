<?php

namespace Bernard\Driver;

use google\appengine\api\taskqueue\PushTask;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Simple driver for google AppEngine. Many features are not supported.
 * It takes a list of array('name' => 'endpoint') to route messages to the
 * correct place.
 *
 * @package Bernard
 */
class AppEngineDriver extends AbstractDriver
{
    protected $queueMap;

    /**
     * @param array $queueMap
     */
    public function __construct(array $queueMap)
    {
        $this->queueMap = $queueMap;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return array_flip($this->queueMap);
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
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message, array $options = [])
    {
        $options = $this->validatePushOptions($options);

        $task = new PushTask($this->resolveEndpoint($queueName), compact('message'), $options);
        $task->add($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
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
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function configurePushOptions(OptionsResolver $resolver)
    {
        //BC layer to support 2.3+ and 2.7+/3.0+ versions
        if (interface_exists('Symfony\Component\OptionsResolver\OptionsResolverInterface')) {
            //2.3+
            $resolver
                ->setOptional(array(
                    'method',
                    'name',
                    'delay_seconds',
                    'header',
                ))
                ->setAllowedValues(array(
                    'method' => array('POST', 'GET', 'HEAD', 'PUT', 'DELETE'),
                ))
            ;
        } else {
            //2.7+
            $resolver
                ->setDefined('method')
                ->setDefined('name')
                ->setDefined('delay_seconds')
                ->setDefined('header')
                ->setAllowedValues(
                    'method', array('POST', 'GET', 'HEAD', 'PUT', 'DELETE')
                )
            ;
        }
    }

    /**
     * @param string $queueName
     *
     * @throws InvalidArgumentException
     */
    protected function resolveEndpoint($queueName)
    {
        if (isset($this->queueMap[$queueName])) {
            return $this->queueMap[$queueName];
        }

        return '/_ah/queue/' . $queueName;
    }
}
