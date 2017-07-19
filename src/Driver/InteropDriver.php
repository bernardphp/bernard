<?php
namespace Bernard\Driver;

use Bernard\Driver;
use Interop\Queue\PsrConsumer;
use Interop\Queue\PsrContext;

final class InteropDriver implements Driver
{
    /**
     * @var PsrContext
     */
    private $context;

    /**
     * @var PsrConsumer[]
     */
    private $consumers;

    /**
     * @param PsrContext $context
     */
    public function __construct(PsrContext $context)
    {
        $this->context = $context;

        $this->consumers = [];
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return [];
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
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queue = $this->context->createQueue($queueName);
        $message = $this->context->createMessage($message);

        $this->context->createProducer()->send($queue, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        if ($message = $this->getQueueConsumer($queueName)->receive($duration * 1000)) {
            return [$message->getBody(), $message];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->getQueueConsumer($queueName)->acknowledge($receipt);
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
        return [];
    }

    /**
     * @param string $queueName
     *
     * @return PsrConsumer
     */
    private function getQueueConsumer($queueName)
    {
        if (false == array_key_exists($queueName, $this->consumers)) {
            $queue = $this->context->createQueue($queueName);

            $this->consumers[$queueName] = $this->context->createConsumer($queue);
        }

        return $this->consumers[$queueName];
    }

    /**
     * @return PsrContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
