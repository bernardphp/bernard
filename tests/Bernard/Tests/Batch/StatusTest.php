<?php

namespace Bernard\Tests\Batch;

use Bernard\Batch\Status;

class StatusTest extends \PHPUnit_Framework_TestCase
{
    public function testIsComplete()
    {
        $status = new Status($total = 10, $failed = 5, $successful = 0);
        $this->assertTrue($status->isRunning());
        $this->assertFalse($status->isComplete());
    }

    public function testIsRunning()
    {
        $status = new Status($total = 10, $failed = 1, $successful = 9);
        $this->assertFalse($status->isRunning());
        $this->assertTrue($status->isComplete());
    }
}
