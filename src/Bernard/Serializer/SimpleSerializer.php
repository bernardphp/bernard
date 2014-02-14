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

        $data = array(
            'args'      => array('name' => $message->getName()) + get_object_vars($message),
            'class'     => bernard_encode_class_name($envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
        );

        return json_encode($data + $envelope->getStamps());
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

        $stamps = array_diff_key($data, array_flip(array('timestamp', 'class', 'args')));
        $envelope = new Envelope(new DefaultMessage($data['args']['name'], $data['args']), $stamps);

        bernard_force_property_value($envelope, 'class', $class);
        bernard_force_property_value($envelope, 'timestamp', $data['timestamp']);

        return $envelope;
    }
}
