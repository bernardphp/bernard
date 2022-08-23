<?php

declare(strict_types=1);

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Is thrown when a Router cannot map an Envelope to a receiver.
 */
final class ReceiverNotFoundException extends \RuntimeException implements Exception
{
}
