<?php

namespace Bernard\Serializer;

use Bernard\Message\DefaultMessage;
use Bernard\Message\Envelope;

/**
 * Very simple Serializer that only supports DefaultMessage
 * message instances. For other Message instances and more
 * advanced needs you should use Symfony or JMS Serializer components.
 *
 * @package Bernard
 */
class NaiveSerializer implements \Bernard\Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        $message = $envelope->getMessage();

        if ($envelope->getClass() != 'Bernard\Message\DefaultMessage') {
            throw new \InvalidArgumentException(strtr('Expected instance of "%expected%" but got "%actual%".', array(
                '%expected%' => 'Bernard\Message\DefaultMessage',
                '%actual%'   => $envelope->getClass(),
            )));
        }

        return json_encode(array(
            'args'      => array('name' => $message->getName()) + get_object_vars($message),
            'class'     => bernard_encode_class_name($envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
            'retries'   => $envelope->getRetries(),
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($serialized)
    {
        // everything is just deserialized into an DefaultMessage
        $data = json_decode($serialized, true);
        $data['class'] = bernard_decode_class_string($data['class']);

        if ($data['class'] !== 'Bernard\Message\DefaultMessage') {
            $data['args']['name'] = current(array_reverse(explode('\\', $data['class'])));
        }

        $envelope = new Envelope(new DefaultMessage($data['args']['name'], $data['args']));

        foreach (array('timestamp', 'retries', 'class') as $name) {
            bernard_force_property_value($envelope, $name, $data[$name]);
        }

        return $envelope;
    }
}
