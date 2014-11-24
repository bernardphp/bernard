<?php

namespace Bernard;

/**
 * Verify class that throws exception when assertion isn't fulfilled.
 *
 * @package Bernard
 */
final class Verify
{
    /**
     * Check the two values are equal
     *
     * @param  mixed $first
     * @param  mixed $second
     * @throws InvalidArgumentException If the two values are not equal
     */
    public static function eq($first, $second)
    {
        if ($first == $second) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected "%s" to equal "%s".', $first, $second));
    }

    /**
     * Check if a value is part of an array
     *
     * @param  mixed  $needle
     * @param  array  $haystack
     * @throws InvalidArgumentException If the value is not part of the array
     */
    public static function any($needle, array $haystack)
    {
        if (in_array($needle, $haystack, true)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected "%s" to one of ["%s"].', $needle, implode('", "', $haystack)));
    }

    /**
     * Check if a value is a callable
     * @param  mixed  $callable
     * @throws InvalidArgumentException If the value is not a callable
     */
    public static function isCallable($callable)
    {
        if (is_callable($callable)) {
            return;
        }

        throw new \InvalidArgumentException('Argument must be a "callable".');
    }

    /**
     * Check if an object is instance of the passed name
     * @param  object  $object
     * @param  string  $name
     * @throws InvalidArgumentException If the object is not an instance of the passed name
     */
    public static function isInstanceOf($object, $name)
    {
        if (is_a($object, $name)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected instance of "%s" but got "%s".', $name, get_class($object)));
    }
}
