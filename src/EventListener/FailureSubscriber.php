<?php

declare(strict_types=1);

namespace Bernard\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Producer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FailureSubscriber implements EventSubscriberInterface
{
    protected $producer;
    protected $name;

    /**
     * @param string $name
     */
    public function __construct(Producer $producer, $name = 'failed')
    {
        $this->producer = $producer;
        $this->name = $name;
    }

    public function onReject(RejectEnvelopeEvent $event): void
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
