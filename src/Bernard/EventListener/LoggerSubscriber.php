<?php

namespace Bernard\EventListener;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\EnvelopeExceptionEvent;
use Psr\Log\LoggerInterface;

class LoggerSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onProduce(EnvelopeEvent $event)
    {
        $this->logger->info('[bernard] produced {envelope} onto {queue}.', array(
            'envelope' => $event->getEnvelope(),
            'queue' => $event->getQueue(),
        ));
    }

    public function onInvoke(EnvelopeEvent $event)
    {
        $this->logger->info('[bernard] invoking receiver for {envelope}.', array(
            'envelope' => $event->getEnvelope(),
        ));
    }

    public function onReject(EnvelopeExceptionEvent $event)
    {
        $this->logger->error('[bernard] caught exception {exception} while processing {envelope}.', array(
            'envelope' => $event->getEnvelope(),
            'exception' => $event->getException(),
        ));
    }

    public static function getSubscribedEvents()
    {
        return array(
            'bernard.produce' => array('onProduce'),
            'bernard.invoke' => array('onInvoke'),
            'bernard.reject' => array('onReject'),
        );
    }
}
