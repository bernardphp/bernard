<?php

namespace Bernard\Exception;

/**
 * Is thrown when a Router tries to map a Envelope to a receiver and
 * cannot be done.
 *
 * @package Bernard
 */
class ReceiverNotFoundException extends \RuntimeException implements Exception
{
}
