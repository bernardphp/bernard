<?php

namespace Raekke\Serializer\Handler;

use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use Raekke\Message\MessageWrapper;
use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
class MessageWrapperHandler implements \JMS\Serializer\Handler\SubscribingHandlerInterface
{
    public function deserializeMessageWrapperFromJson(JsonDeserializationVisitor $visitor, array $data, $type)
    {
    }

    public static function getSubscribingMethods()
    {
        return array();
    }
}
