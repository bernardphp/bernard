<?php

namespace Bernard\EventListener;

use Bernard\Event\ConsumerCycleEvent;
use Bernard\Event\EnvelopeEvent;
use Bernard\Event\RejectEnvelopeEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Bernard
 */
class MessageLimitSubscriber implements EventSubscriberInterface
{
    protected $messageLimit;
    protected $messageCount = 0;
    protected $countFailures = true;

    /**
     * @param integer $messageLimit
     * @param boolean $countFailures
     */
    public function __construct($messageLimit, $countFailures = true)
    {
        $this->messageLimit = $messageLimit;
        $this->countFailures = (bool) $countFailures;
    }

    /**
     * Check if the consumer passed the limit
     *
     * @param ConsumerCycleEvent $event
     */
    public function onCycle(ConsumerCycleEvent $event)
    {
        if ($this->messageLimit <= $this->messageCount) {
            $event->shutdown();
        }
    }

    /**
     * Counts an invoke
     *
     * @param EnvelopeEvent $event
     */
    public function onInvoke(EnvelopeEvent $event)
    {
        $this->messageCount++;
    }

    /**
     * Counts a reject
     *
     * @param RejectEnvelopeEvent $event
     */
    public function onReject(RejectEnvelopeEvent $event)
    {
        if ($this->countFailures) {
            $this->messageCount++;
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'bernard.cycle' => array('onCycle'),
            'bernard.invoke' => array('onInvoke'),
            'bernard.reject' => array('onReject'),
        );
    }
}
