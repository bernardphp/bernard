<?php

namespace Bernard\Exception;

/**
 * Thrown when driver does not support requested feature.
 */
class NotImplementedException extends \BadMethodCallException implements Exception
{
}
