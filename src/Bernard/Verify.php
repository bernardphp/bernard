<?php

namespace Bernard;

/**
 * Verify class that throws exception when assertion isnt fulfilled.
 *
 * @package Bernard
 */
final class Verify
{
    public static function isCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Argument must be a "callable".');
        }
    }
}
