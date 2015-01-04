<?php

namespace Bernard;

use Assert\Assertion;

/**
 * Verify class that throws exception when assertion isn't fulfilled.
 *
 * @package Bernard
 */
final class Verify extends Assertion
{
    /**
     * Check if a value is part of an array
     *
     * @param mixed $needle
     * @param array $haystack
     *
     * @throws InvalidArgumentException If the value is not part of the array
     */
    public static function any($needle, array $haystack)
    {
        self::choice($needle, $haystack, 'Expected "%s" to one of ["%s"].');
    }

    /**
     * Check if a value is a callable
     *
     * @param mixed $callable
     *
     * @throws InvalidArgumentException If the value is not a callable
     */
    public static function isCallable($callable)
    {
        if (is_callable($callable)) {
            return;
        }

        throw new \InvalidArgumentException('Argument must be a "callable".');
    }
}
