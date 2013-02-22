<?php

namespace Raekke\Message;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Metadata\PropertyMetadata;

/**
 * @package Raekke
 */
class DefaultMessage extends Message
{
    protected $name;

    public function __construct($name, array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->name = preg_replace('/(^([0-9]+))|([^[:alnum:]-_+])/i', '', $name);
    }

    public function getName()
    {
        return $this->name;
    }

    public function serializeToJson(AbstractVisitor $visitor)
    {
        return get_object_vars($this);
    }

    public function deserializeFromJson(AbstractVisitor $visitor, array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}
