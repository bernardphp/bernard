<?php

use Bernard\Message;

function bernard_encode_class_name($className)
{
    return str_replace('\\', ':', $className);
}

function bernard_decode_class_string($classString)
{
    return str_replace(':', '\\', $classString);
}

function bernard_guess_queue(Message $message)
{
    return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $message->getName())), '-');
}
