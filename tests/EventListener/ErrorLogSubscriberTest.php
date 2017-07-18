<?php

namespace Bernard\Tests\EventListener;

use Bernard\Event\RejectEnvelopeEvent;
use Bernard\EventListener\ErrorLogSubscriber;

class ErrorLogSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private $envelope;
    private $queue;
    private $producer;
    private $subscriber;
    private $iniErrorLog;
    private $errorLogFile;

    public function setUp()
    {
        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("HHVM does not support `ini_set('error_log', '/path/to/log')`");
        }

        $this->envelope = $this->getMockBuilder('Bernard\Envelope')
            ->disableOriginalConstructor()->getMock();
        $this->queue = $this->createMock('Bernard\Queue');
        $this->producer = $this->getMockBuilder('Bernard\Producer')->disableOriginalConstructor()->getMock();
        $this->subscriber = new ErrorLogSubscriber($this->producer, 'failures');
        $this->iniErrorLog = ini_get('error_log');
        $this->errorLogFile = tempnam(sys_get_temp_dir(), 'phpunit');
        ini_set('error_log', $this->errorLogFile);
        ini_set('error_log', $this->errorLogFile);
    }

    public function tearDown()
    {
        ini_set('error_log', $this->iniErrorLog);
        unlink($this->errorLogFile);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            'bernard.reject' => ['onReject'],
        ];
        $actual = ErrorLogSubscriber::getSubscribedEvents();
        $this->assertEquals($expected, $actual);
    }

    public function testOnRejectException()
    {

        $this->envelope->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $error = new \Exception('bar');
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $error);
        $expected = ' [bernard] caught exception Exception::bar while processing foo.';
        $this->subscriber->onReject($event);
        $actual = trim(file_get_contents($this->errorLogFile));
        $this->assertStringEndsWith($expected, $actual);
    }

    /**
     * @requires PHP 7.0
     */
    public function testOnRejectError()
    {
        $this->envelope->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $error = new \TypeError('bar');
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $error);
        $expected = ' [bernard] caught exception TypeError::bar while processing foo.';
        $this->subscriber->onReject($event);
        $actual = trim(file_get_contents($this->errorLogFile));
        $this->assertStringEndsWith($expected, $actual);
    }

    public function testOnRejectObject()
    {
        $this->envelope->expects($this->once())
            ->method('getName')
            ->willReturn('foo');
        $error = new \stdClass;
        $event = new RejectEnvelopeEvent($this->envelope, $this->queue, $error);
        $expected = ' [bernard] caught unknown error type stdClass while processing foo.';
        $this->subscriber->onReject($event);
        $actual = trim(file_get_contents($this->errorLogFile));
        $this->assertStringEndsWith($expected, $actual);
    }
}
