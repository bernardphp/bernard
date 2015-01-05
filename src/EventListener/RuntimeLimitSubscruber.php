<?php

namespace Bernard\EventListener;

use Bernard\Event\ConsumerCycleEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package Bernard
 */
class RuntimeLimitSubscriber implements EventSubscriberInterface
{
    protected $timeLimit;

    /**
     * @param integer $timeLimit
     */
    public function __construct($timeLimit)
    {
        $this->timeLimit = $timeLimit;
    }

    /**
     * Check if the consumer passed the limit
     *
     * @param ConsumerCycleEvent $event
     */
    public function onCycle(ConsumerCycleEvent $event)
    {
        if ($this->timeLimit <= microtime(true)) {
            $event->shutdown();
        }
    }

    /**
     * Counts an invoke
     *
     * @param Event $event
     */
    public function onStart(Event $event)
    {
        $this->timeLimit += microtime(true);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            'bernard.cycle' => array('onCycle'),
            'bernard.start' => array('onStart'),
        );
    }
}
