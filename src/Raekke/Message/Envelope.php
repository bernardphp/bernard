<?php

namespace Raekke\Message;

use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;

/**
 * Wraps a MessageInterface with metadata that can be used for automatic retry
 * or inspection.
 *
 * @package Raekke
 */
class Envelope
{
    protected $message;
    protected $name;
    protected $class;
    protected $timestamp;
    protected $retries = 0;

    /**
     * @param MessageInterface $message
     */
    public function __construct(MessageInterface $message)
    {
        $this->message   = $message;
        $this->class     = get_class($message);
        $this->name      = $message->getName();
        $this->timestamp = time();
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return integer
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return integer
     */
    public function getRetries()
    {
        return $this->retries;
    }

    public function incrementRetries()
    {
        $this->retries += 1;
    }

    /**
     * @param AbstractVisitor $visitor
     * @param array $data
     * @param Context $context
     */
    public function serializeToJson(AbstractVisitor $visitor, $data, Context $context)
    {
        $type = array('name' => $this->class, 'params' => array());
        $data = array(
            'args'      => $context->accept($this->message, $type),
            'name'      => $this->name,
            'class'     => $this->class,
            'timestamp' => $this->timestamp,
            'retries'   => $this->retries,
        );

        $visitor->setRoot($data);

        return $data;
    }

    /**
     * @param AbstractVisitor $visitor
     * @param array $data
     * @param Context $context
     */
    public function deserializeFromJson(AbstractVisitor $visitor, array $data, Context $context)
    {
        $this->class     = $data['class'];
        $this->name      = $data['name'];
        $this->timestamp = $data['timestamp'];
        $this->retries   = $data['retries'];

        $type = array(
            'name' => 'Raekke\Message\DefaultMessage',
            'params' => array(),
        );

        if (class_exists($this->class)) {
            $type['name'] = $this->class;
        }

        $this->message = $context->accept(array_merge($data['args'], array('name' => $this->name)), $type);
    }
}
