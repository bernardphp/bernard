<?php

namespace Bernard\Encoder;

use Bernard\Message;

interface Normalizer
{
    public function normalize(Message $message);

    public function denormalize(array $data);
}
