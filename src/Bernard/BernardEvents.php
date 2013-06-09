<?php

namespace Bernard;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent

final class BernardEvents extends SymfonyEvent
{
	/**
	 * The PRODUCE event occurs before enqueuing a message.
	 */
	const PRODUCE = 'bernard.produce';

	/**
	 * The CONSUME event occurs when a message is dequeued by the consumer.
	 */
	const CONSUME = 'bernard.consume';
}