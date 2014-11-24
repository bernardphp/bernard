<?php

namespace Bernard\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\Envelope;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Exception;

class ErrorLogSubscriber implements EventSubscriberInterface
{
    public function onReject(RejectEnvelopeEvent $event)
    {
        error_log($this->format($event->getEnvelope(), $event->getException()));
    }

    protected function format(Envelope $envelope, Exception $exception)
    {
        $replacements = array(
            '{class}' => get_class($exception),
            '{message}' => $exception->getMessage(),
            '{envelope}' => $envelope->getName(),
        );

        return strtr('[bernard] caught exception {class}::{message} while processing {envelope}.', $replacements);
    }

    public static function getSubscribedEvents()
    {
        return array(
            'bernard.reject' => array('onReject'),
        );
    }
}
