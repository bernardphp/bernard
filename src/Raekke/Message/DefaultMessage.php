<?php

namespace Raekke\Message;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * @package Raekke
 */
class DefaultMessage extends Message
{
    protected $messageName;

    public function __construct($messageName, array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->messageName = preg_replace(array(
            '/([^[:alnum:]-_+])/i',
            '/^([0-9]+)/',
        ), '', $messageName);
    }

    public function getName()
    {
        return $this->messageName;
    }

    public function serializeToJson(AbstractVisitor $visitor)
    {
        $data = array();

        foreach (get_object_vars($this) as $k => $v) {
            $data[$k == 'messageName' ? 'message_name' : $k] = $v;
        }

        return $data;
    }

    public function deserializeFromJson(AbstractVisitor $visitor, array $data)
    {
        foreach ($data as $k => $v) {
            $this->{$k == 'message_name' ? 'messageName' : $k} = $v;
        }
    }
}
