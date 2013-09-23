<?php

namespace Bernard\Serializer;

use Bernard\Message\DefaultMessage;
use Bernard\Envelope;
use Bernard\Verify;

/**
 * Very simple Serializer that only supports the core message types
 * DefaultMessage and FailedMessage. For other Message instances and more
 * advanced needs you should use Symfony or JMS Serializer components.
 *
 * @package Bernard
 */
class SimpleSerializer implements \Bernard\Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        Verify::any($envelope->getClass(), array('Bernard\Message\DefaultMessage', 'Bernard\Message\FailedMessage'));

        $message = $envelope->getMessage();

        return json_encode(array(
            'args'      => array('name' => $message->getName()) + get_object_vars($message),
            'class'     => bernard_encode_class_name($envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($serialized)
    {
        // everything is just deserialized into an DefaultMessage
        $data = json_decode($serialized, true);
        $class = bernard_decode_class_string($data['class']);

        if ($class !== 'Bernard\Message\DefaultMessage') {
            $data['args']['name'] = substr(strrchr($class, '\\'), 1);
        }

        $envelope = new Envelope(new DefaultMessage($data['args']['name'], $data['args']));

        bernard_force_property_value($envelope, 'class', $class);
        bernard_force_property_value($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }
}
