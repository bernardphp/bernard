<?php

declare(strict_types=1);

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Thrown when someone tries to do an illegal operation on a queue
 * (eg. enqueue a message when the queue is already closed).
 */
final class InvalidOperationException extends \Exception implements Exception
{
}
