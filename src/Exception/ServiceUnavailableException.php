<?php

declare(strict_types=1);

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Thrown when a service behind the driver implementation is unavailable.
 */
final class ServiceUnavailableException extends \RuntimeException implements Exception
{
}
