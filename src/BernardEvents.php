<?php

declare(strict_types=1);

namespace Bernard;

/**
 * Contains all events dispatched by bernard.
 */
final class BernardEvents
{
    /**
     * The PING event occurs each time when bernard starts the consume loop.
     *
     * The event listener method receives a Bernard\Event\PingEvent instance.
     */
    public const PING = 'bernard.ping';

    /**
     * The INVOKE event occurs each time when the consumption of a message is started.
     *
     * The event listener method receives a Bernard\Event\EnvelopeEvent instance.
     */
    public const INVOKE = 'bernard.invoke';

    /**
     * The ACKNOWLEDGE event occurs when a message is acknowledged.
     *
     * The event listener method receives a Bernard\Event\EnvelopeEvent instance.
     */
    public const ACKNOWLEDGE = 'bernard.acknowledge';

    /**
     * The REJECT event occurs when a message is rejected.
     *
     * This event allows you to handle the exception occured while consuming a message.
     * The event listener method receives a Bernard\Event\RejectEnvelopeEvent instance.
     */
    public const REJECT = 'bernard.reject';

    /**
     * The PRODUCE event occurs when a message is produced.
     *
     * The event listener method receives a Bernard\Event\EnvelopeEvent instance.
     */
    public const PRODUCE = 'bernard.produce';
}
