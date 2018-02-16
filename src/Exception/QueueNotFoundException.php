<?php

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Thrown when queue does not exist.
 */
class QueueNotFoundException extends \RuntimeException implements Exception
{
}
