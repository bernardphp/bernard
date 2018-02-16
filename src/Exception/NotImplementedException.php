<?php

namespace Bernard\Exception;

use Bernard\Exception;

/**
 * Thrown when driver does not support requested feature.
 */
class NotImplementedException extends \BadMethodCallException implements Exception
{
}
