<?php

namespace Bernard\EventListener;

use Bernard\QueueFactory;
use Bernard\Event\RejectEnvelopeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Bernard
 */
class FailureSubscriber implements EventSubscriberInterface
{
    /**
     * @var QueueFactory
     */
    protected $queues;

    /**
     * @var string
     */
    protected $name;

    /**
     * @param QueueFactory $queues
     * @param string       $name
     */
    public function __construct(QueueFactory $queues, $name = 'failed')
    {
        $this->queues = $queues;
        $this->name = $name;
    }

    /**
     * @param RejectEnvelopeEvent $event
     */
    public function onReject(RejectEnvelopeEvent $event)
    {

        $envelope = $event->getEnvelope();

        $event->getQueue()->acknowledge($envelope);
        $this->queues->create($this->name)->enqueue($envelope);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'bernard.reject' => array('onReject'),
        );
    }
}
