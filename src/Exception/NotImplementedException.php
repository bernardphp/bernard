<?php

namespace Bernard\Exception;

/**
 * Thrown when driver does not support requested feature
 * @package Bernard
 */
class NotImplementedException extends \BadMethodCallException implements Exception
{
}
