<?php

namespace Bernard\Tests\Batch;

use Bernard\Batch;

class AbstractStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testCallsFindWithId()
    {
        $storage = $this->getMockForAbstractClass('Bernard\Batch\AbstractStorage');
        $storage->expects($this->once())->method('find')->with('my-id');

        $storage->reload(new Batch('my-id'));
    }
}
