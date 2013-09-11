<?php

/**
 * @param  string $className
 * @return string
 */
function bernard_encode_class_name($className)
{
    return str_replace('\\', ':', $className);
}

/**
 * @param  string $encodedClassName
 * @return string
 */
function bernard_decode_class_string($classString)
{
    return str_replace(':', '\\', $classString);
}

function bernard_force_property_value($object, $property, $value)
{
    $property = new \ReflectionProperty($object, $property);
    $property->setAccessible(true);
    $property->setValue($object, $value);
}
