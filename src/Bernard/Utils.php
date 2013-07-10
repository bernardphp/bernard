<?php

namespace Bernard;

/**
 * Utility class
 *
 * @package Bernard
 */
class Utils
{
    private function __construct()
    {
    }

    /**
     * @param string $className
     * @return string
     */
    public static function encodeClassName($className)
    {
        return str_replace('\\', ':', $className);
    }

    /**
     * @param string $encodedClassName
     * @return string
     */
    public static function decodeClassString($classString)
    {
        return str_replace(':', '\\', $classString);
    }
}
