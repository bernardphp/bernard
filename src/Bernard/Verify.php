<?php

namespace Bernard;

/**
 * Verify class that throws exception when assertion isn't fulfilled.
 *
 * @package Bernard
 */
final class Verify
{
    public static function eq($first, $second)
    {
        if ($first == $second) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected "%s" to equal "%s".', $first, $second));
    }

    public static function any($needle, array $haystack)
    {
        if (in_array($needle, $haystack, true)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected "%s" to one of ["%s"].', $needle, implode('", "', $haystack)));
    }

    public static function isCallable($callable)
    {
        if (is_callable($callable)) {
            return;
        }

        throw new \InvalidArgumentException('Argument must be a "callable".');
    }

    public static function isInstanceOf($object, $name)
    {
        if (is_a($object, $name)) {
            return;
        }

        throw new \InvalidArgumentException(sprintf('Expected instance of "%s" but got "%s".', $name, get_class($object)));
    }
}
