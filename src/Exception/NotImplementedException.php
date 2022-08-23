<?php

declare(strict_types=1);

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Thrown when driver does not support requested feature.
 */
final class NotImplementedException extends \BadMethodCallException implements Exception
{
}
