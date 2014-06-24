<?php

namespace Bernard\Tests;

use Bernard\Batch;
use Bernard\Batch\Status;
use Bernard\Message;

class BatchTest extends \PHPUnit_Framework_TestCase
{
    public function testBatchGeneratesKey()
    {
        $batch = new Batch();

        $this->assertInternalType('string', $batch->getName());
    }

    public function testStatus()
    {
        $batch = new Batch(null, null, new Status(10, 5, 3));

        $this->assertEquals(new Status(10, 5, 3), $batch->getStatus());
    }

    public function testEnvelopeIsCreatedWithBatchStamp()
    {
        $batch = new Batch('my-id');
        $batch->assign($this->getMock('Bernard\Message'));

        $envelopes = $batch->flush();

        $this->assertEquals('my-id', $envelopes[0]->getStamp('batch'));
    }

    public function testReuseKey()
    {
        $batch = new Batch('my-key');

        $this->assertEquals('my-key', $batch->getName());
    }

    public function testAssigningMessageUpdatesTotal()
    {
        $message = $this->getMock('Bernard\Message');

        $batch = new Batch('my-id');
        $batch->assign($message);

        $this->assertEquals(new Status(1, 0, 0), $batch->getStatus());
    }

    public function testFlushResetArray()
    {
        $message = $this->getMock('Bernard\Message');

        $batch = new Batch;
        $batch->assign($message);

        $this->assertEquals(new Status(1, 0, 0), $batch->getStatus());

        $envelopes = $batch->flush();

        $this->assertContainsOnlyInstancesOf('Bernard\Envelope', $envelopes);
        $this->assertCount(1, $envelopes);

        $this->assertCount(0, $batch->flush());
    }
}
