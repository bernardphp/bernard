<?php

namespace Bernard\EventListener;

use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Bernard
 */
class LoggerSubscriber implements EventSubscriberInterface
{
    protected $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EnvelopeEvent $event
     */
    public function onProduce(EnvelopeEvent $event)
    {
        $this->logger->info('[bernard] produced {envelope} onto {queue}.', array(
            'envelope' => $event->getEnvelope(),
            'queue' => $event->getQueue(),
        ));
    }

    /**
     * @param EnvelopeEvent $event
     */
    public function onInvoke(EnvelopeEvent $event)
    {
        $this->logger->info('[bernard] invoking receiver for {envelope}.', array(
            'envelope' => $event->getEnvelope(),
        ));
    }

    /**
     * @param  RejectEnvelopeEvent $event
     */
    public function onReject(RejectEnvelopeEvent $event)
    {
        $this->logger->error('[bernard] caught exception {exception} while processing {envelope}.', array(
            'envelope' => $event->getEnvelope(),
            'exception' => $event->getException(),
        ));
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'bernard.produce' => array('onProduce'),
            'bernard.invoke' => array('onInvoke'),
            'bernard.reject' => array('onReject'),
        );
    }
}
