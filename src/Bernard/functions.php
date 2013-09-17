<?php

use Bernard\Message;

/**
 * @param  string $className
 * @return string
 */
function bernard_encode_class_name($className)
{
    return str_replace('\\', ':', $className);
}

/**
 * @param  string $classString
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

function bernard_guess_queue(Message $message)
{
    return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $message->getName())), '-');
}
