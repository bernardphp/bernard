<?php

namespace Bernard\Tests\Message;

use Bernard\Message\DefaultMessage;

class DefaultMessageTest extends PlainMessageTest
{
    protected function createMessage($name, array $data = [])
    {
        return new DefaultMessage($name, $data);
    }
}
