<?php

namespace Raekke\Message;

use Raekke\Message;
use JMS\Serializer\AbstractVisitor;
use JMS\Serializer\Context;

/**
 * Wraps a Message with metadata that can be used for automatic retry
 * or inspection.
 *
 * @package Raekke
 */
final class Envelope
{
    protected $message;
    protected $class;
    protected $timestamp;
    protected $retries = 0;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message   = $message;
        $this->class     = get_class($message);
        $this->timestamp = time();
    }

    /**
     * @return Message
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
        return $this->message->getName();
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

    /**
     * Increment number of retries
     */
    public function incrementRetries()
    {
        $this->retries += 1;
    }

    /**
     * @param  AbstractVisitor $visitor
     * @param  null            $data
     * @param  Context         $context
     * @return array
     */
    public function serializeToJson(AbstractVisitor $visitor, $data, Context $context)
    {
        $type = array('name' => $this->class, 'params' => array());
        $data = array(
            'args'      => $context->accept($this->message, $type),
            'class'     => str_replace('\\', ':', $this->class),
            'timestamp' => $this->timestamp,
            'retries'   => $this->retries,
        );

        $visitor->setRoot($data);

        return $data;
    }

    /**
     * @param AbstractVisitor $visitor
     * @param array           $data
     * @param Context         $context
     */
    public function deserializeFromJson(AbstractVisitor $visitor, array $data, Context $context)
    {
        $this->class     = str_replace(':', '\\', $data['class']);
        $this->timestamp = $data['timestamp'];
        $this->retries   = $data['retries'];

        $type = array(
            'name' => 'Raekke\Message\DefaultMessage',
            'params' => array(),
        );

        // This will allow DefaultMessage to be used for introspection where the default classes
        // are not available (like when viewed in Juno)
        if (class_exists($this->class)) {
            $type['name'] = $this->class;
        }

        $this->message = $context->accept($data['args'], $type);
    }
}
