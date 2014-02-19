<?php

namespace Bernard\EventListener;

use Bernard\Event\EnvelopeExceptionEvent;

class ErrorLogSubscriber implements \Symfony\Component\EventDispatcher\EventSubscriberInterface
{
    public function onReject(EnvelopeExceptionEvent $event)
    {
        $exception = $event->getException();

        error_log(sprintf('[bernard] caught exception %s::%s while processing %s.', 
           get_class($exception), $exception->getMessage(), $event->getEnvelope()->getName()));
    }

    public static function getSubscribedEvents()
    {
        return array(
            'bernard.reject' => array('onReject'),
        );
    }
}
