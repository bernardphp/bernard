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
class NativeSerializer implements \Bernard\Serializer
{
    /**
     * {@inheritDoc}
     */
    public function serialize(Envelope $envelope)
    {
        if (!$envelope->getMessage() instanceof DefaultMessage) {
            die('only dfault message');
        }

        return array(
            'args'      => array('name' => $envelope->getName()) + get_object_vars($envelope->getMessage()),
            'class'     => str_replace('\\', ':', $envelope->getClass()),
            'timestamp' => $envelope->getTimestamp(),
            'retries'   => $envelope->getRetries(),
        );
    }

    /**
     * {@inheritDoc}
     */
    public function deserialize($serialized)
    {
        // everything is just deserialized into an DefaultMessage
        $data = json_decode($serialized, true);
        $data['class'] = str_replace(':', '\\', $data['class']);

        if ($data['class'] !== 'Bernard\Message\DefaultMessage') {
            $data['args']['name'] = current(array_reverse(explode('\\', $data['class'])));
        }

        $envelope = new Envelope(new DefaultMessage($data['args']['name'], $data['args']));

        foreach (array('timestamp', 'retries', 'class') as $name) {
            $property = new \ReflectionProperty($envelope, $name);
            $property->setAccessible(true);
            $property->setValue($envelope, $data[$name]);
        }

        return $envelope;
    }
}
