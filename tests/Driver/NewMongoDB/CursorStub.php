<?php

namespace Bernard\Tests\Driver\NewMongoDB;

use ArrayIterator;


class CursorStub
{
    public function toArray(){
        return [
            ['message' => 'message1'],
            ['message' => 'message2'],
        ];
    }
}
