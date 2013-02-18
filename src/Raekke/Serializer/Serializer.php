<?php

namespace Raekke\Serializer;

use Raekke\Message\MessageInterface;

/**
 * @package Raekke
 */
class Serializer
{
    /**
     * @param MessageInterface $message
     */
    public function serialize(MessageInterface $message)
    {
        return json_encode(array(
            'class'     => get_class($message),
            'data'      => serialize($message),
            'timestamp' => time(),
        ));
    }

    /**
     * @param string $content
     * @Param boolean $onlyObject
     */
    public function deserialize($content, $onlyObject = true)
    {
        $json = json_decode($content);

        if ($onlyObject) {
            return unserialize($json->data);
        }

        return array(
            'class' => $json->class,
            'message' => unserialize($json->data),
            'timestamp' => $json->timestamp,
        );
    }
}
