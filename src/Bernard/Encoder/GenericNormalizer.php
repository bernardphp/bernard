<?php

namespace Bernard\Encoder;

use Bernard\Message;
use Bernard\Message\DefaultMessage;
use Bernard\Verify;

class GenericNormalizer implements Normalizer
{
    public function normalize(Message $message)
    {
        Verify::isInstanceOf($message, 'Bernard\Message\DefaultMessage');

        return array(
            'name' => $message->getName(),
            'arguments' => $message->all(),
        );
    }

    public function denormalize($class, array $data)
    {
        Verify::eq($class, 'Bernard\Message\DefaultMessage');

        return new DefaultMessage($data['name'], $data['arguments']);
    }
}
