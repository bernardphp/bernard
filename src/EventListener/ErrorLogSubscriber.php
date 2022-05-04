<?php

declare(strict_types=1);

namespace Bernard\EventListener;

use Bernard\Envelope;
use Bernard\Event\RejectEnvelopeEvent;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Throwable;

class ErrorLogSubscriber implements EventSubscriberInterface
{
    public function onReject(RejectEnvelopeEvent $event): void
    {
        error_log($this->format($event->getEnvelope(), $event->getException()));
    }

    /**
     * @param mixed $exception
     *
     * @return string
     */
    protected function format(Envelope $envelope, $exception)
    {
        if ($exception instanceof Exception || $exception instanceof Throwable) {
            $replacements = [
                '{class}' => $exception::class,
                '{message}' => $exception->getMessage(),
                '{envelope}' => $envelope->getName(),
            ];

            return strtr('[bernard] caught exception {class}::{message} while processing {envelope}.', $replacements);
        }

        $replacements = [
            '{type}' => \is_object($exception) ? $exception::class : \gettype($exception),
            '{envelope}' => $envelope->getName(),
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
