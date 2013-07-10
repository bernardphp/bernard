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

    /**
     * Uses reflection to force set a value on an object property.
     *
     * @param object $object
     * @param string $property
     * @param mixed $value
     */
    public static function forceObjectPropertyValue($object, $property, $value)
    {
        $property = new \ReflectionProperty($object, $property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
