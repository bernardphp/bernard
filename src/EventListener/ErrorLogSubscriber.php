<?php

namespace Bernard\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Envelope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Exception;

/**
 * @package Bernard
 */
class ErrorLogSubscriber implements EventSubscriberInterface
{
    /**
     * @param RejectEnvelopeEvent $event
     */
    public function onReject(RejectEnvelopeEvent $event)
    {
        error_log($this->format($event->getEnvelope(), $event->getException()));
    }

    /**
     * @param Envelope  $envelope
     * @param Exception $exception
     */
    protected function format(Envelope $envelope, Exception $exception)
    {
        $replacements = [
            '{class}' => get_class($exception),
            '{message}' => $exception->getMessage(),
            '{envelope}' => $envelope->getName(),
        ];

        return strtr('[bernard] caught exception {class}::{message} while processing {envelope}.', $replacements);
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
