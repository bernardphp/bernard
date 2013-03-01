<?php

namespace Raekke\Message;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;

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

    public function serializeToJson(AbstractVisitor $visitor, $data, Context $context)
    {
        $data = get_object_vars($this);
        $data['name'] = null;

        return array_filter($data);
    }

    public function deserializeFromJson(AbstractVisitor $visitor, array $data)
    {
        foreach ($data as $k => $v) {
            $this->$k = $v;
        }
    }
}
