<?php
namespace Bernard\Driver;

use Bernard\Driver;
use Interop\Amqp\AmqpContext;
use Interop\Amqp\AmqpQueue;

final class AmqpInteropDriver implements Driver
{
    /**
     * @var AmqpContext
     */
    private $context;

    /**
     * @var InteropDriver
     */
    private $interopDriver;

    /**
     * @param InteropDriver $interopDriver
     */
    public function __construct(InteropDriver $interopDriver)
    {
        $this->interopDriver = $interopDriver;
        $this->context = $interopDriver->getContext();

        if (false == $this->context instanceof AmqpContext) {
            throw new \LogicException(sprintf(
                'The context must be instance of "%s" but got "%s"',
                AmqpContext::class,
                get_class($this->context)
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        return $this->interopDriver->listQueues();
    }

    /**
     * {@inheritdoc}
     */
    public function createQueue($queueName)
    {
        $this->context->declareQueue($this->createAmqpQueue($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        return $this->context->declareQueue($this->createAmqpQueue($queueName));
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $this->interopDriver->pushMessage($queueName, $message);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        return $this->interopDriver->popMessage($queueName, $duration);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $this->interopDriver->acknowledgeMessage($queueName, $receipt);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return $this->interopDriver->peekQueue($queueName, $index, $limit);
    }

    /**
     * {@inheritdoc}
     */
    public function removeQueue($queueName)
    {
        $queue = $this->createAmqpQueue($queueName);

        $this->context->deleteQueue($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return $this->interopDriver->info();
    }

    /**
     * @param $queueName
     *
     * @return AmqpQueue
     */
    private function createAmqpQueue($queueName)
    {
        $queue = $this->context->createQueue($queueName);
        $queue->addFlag(AmqpQueue::FLAG_DURABLE);

        return $queue;
    }

    /**
     * @return AmqpContext
     */
    public function getContext()
    {
        return $this->context;
    }
}
