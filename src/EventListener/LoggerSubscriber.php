<?php

declare(strict_types=1);

namespace Bernard\EventListener;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LoggerSubscriber implements EventSubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onProduce(EnvelopeEvent $event): void
    {
        $this->logger->info('[bernard] produced {envelope} onto {queue}.', [
            'envelope' => $event->getEnvelope(),
            'queue' => $event->getQueue(),
        ]);
    }

    public function onInvoke(EnvelopeEvent $event): void
    {
        $this->logger->info('[bernard] invoking receiver for {envelope}.', [
            'envelope' => $event->getEnvelope(),
        ]);
    }

    public function onReject(RejectEnvelopeEvent $event): void
    {
        $this->logger->error('[bernard] caught exception {exception} while processing {envelope}.', [
            'envelope' => $event->getEnvelope(),
            'exception' => $event->getException(),
        ]);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'bernard.produce' => ['onProduce'],
            'bernard.invoke' => ['onInvoke'],
            'bernard.reject' => ['onReject'],
        ];
    }
}
