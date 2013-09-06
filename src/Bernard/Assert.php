<?php

namespace Bernard;

/**
 * Assert class that throws exception when assertion isnt fulfilled.
 *
 * @package Bernard
 */
final class Assert
{
    public static function assertCallable($callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Argument must be a "callable".');
        }
    }
}
