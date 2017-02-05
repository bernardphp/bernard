<?php

namespace Bernard\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Envelope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Exception;
use Throwable;

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
     * @param Envelope $envelope
     * @param mixed $exception
     *
     * @return string
     */
    protected function format(Envelope $envelope, $exception)
    {
        if ($exception instanceof Exception || $exception instanceof Throwable) {
            $replacements = [
                '{class}' => get_class($exception),
                '{message}' => $exception->getMessage(),
                '{envelope}' => $envelope->getName(),
            ];

            return strtr('[bernard] caught exception {class}::{message} while processing {envelope}.', $replacements);
        }

        $replacements = [
            '{type}' => is_object($exception) ? get_class($exception) : gettype($exception),
            '{envelope}' => $envelope->getName()
        ];
        return strtr('[bernard] caught unknown error type {type} while processing {envelope}.', $replacements);
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
