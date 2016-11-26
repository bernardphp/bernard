<?php

namespace Bernard\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Bernard
 */
class FailureSubscriber implements EventSubscriberInterface
{
    protected $producer;
    protected $name;

    /**
     * @param Producer $producer
     * @param string $name
     */
    public function __construct(Producer $producer, $name = 'failed')
    {
        $this->producer = $producer;
        $this->name = $name;
    }

    /**
     * @param RejectEnvelopeEvent $event
     */
    public function onReject(RejectEnvelopeEvent $event)
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        $this->producer->produce($message, $this->name);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'bernard.reject' => ['onReject'],
        ];
    }
}
