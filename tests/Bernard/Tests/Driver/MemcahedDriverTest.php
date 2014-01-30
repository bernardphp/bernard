<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\MemcachedDriver;
use Bernard\Exception\DriverException;

class MemcachedDriverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('memcached')) {
            $this->markTestSkipped('"memcached" extension is not loaded.');
        }
    }

    /**
     * Test that given a clean memcached the queue will be setup correctly
     */
    public function testCreateEmptyQueueList()
    {
        $memcached = $this->getMock('Memcached', array('get', 'set', 'add'));

        $memcached->expects($this->exactly(3))
                  ->method('get')
                  ->will($this->returnCallback(function($key) {
                        if ($key == 'bernard_queuelist') return false;
                        if ($key == 'bernard_send-newsletter_head') return false;
                        if ($key == 'bernard_send-newsletter_tail') return false;
                    }))
        ;

        $memcached->expects($this->once())
                  ->method('set')
                  ->with('bernard_queuelist', json_encode(array('send-newsletter')))
                  ->will($this->returnValue(true))
        ;

        $memcached->expects($this->exactly(2))
                  ->method('add')
                  ->with($this->logicalOr('bernard_send-newsletter_head', 0, 'bernard_send-newsletter_tail', 0))
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $driver->createQueue('send-newsletter');
    }

    /** 
     * Test that given a existing queue list, a new queue is created correctly
     */
    public function testCreateNonEmptyQueueList()
    {
        $memcached = $this->getMock('Memcached', array('get', 'cas', 'add'));

        $memcached->expects($this->exactly(3))
                  ->method('get')
                  ->will($this->returnCallback(function($key, $cache_cb, &$casToken) {
                        $casToken = 1234.1234;
                        if ($key == 'bernard_queuelist') return json_encode(array('some-other-queue'));
                        if ($key == 'bernard_send-newsletter_head') return false;
                        if ($key == 'bernard_send-newsletter_tail') return false;
                    }))
        ;
        
        $memcached->expects($this->once())
                  ->method('cas')
                  ->with(1234.1234, 'bernard_queuelist', json_encode(array('some-other-queue', 'send-newsletter')))
                  ->will($this->returnValue(true))
        ;

        $memcached->expects($this->exactly(2))
                  ->method('add')
                  ->with($this->logicalOr('bernard_send-newsletter_head', 0, 0, 'bernard_send-newsletter_tail', 0, 0))
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $driver->createQueue('send-newsletter');
    }

    /**
     * Test that an existing queue will have a its head restored to 0 if lost
     */
    public function testCreateExistingQueueMissingHead()
    {
        $memcached = $this->getMock('Memcached', array('get', 'add'));

        $memcached->expects($this->exactly(3))
                  ->method('get')
                  ->will($this->returnCallback(function($key) {
                        if ($key == 'bernard_queuelist') return json_encode(array('send-newsletter'));
                        if ($key == 'bernard_send-newsletter_head') return false;
                        if ($key == 'bernard_send-newsletter_tail') return 123;
                    }))
        ;

        $memcached->expects($this->exactly(1))
                  ->method('add')
                  ->with('bernard_send-newsletter_head', 0, 0)
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $driver->createQueue('send-newsletter');
    }

    /** 
     * Test that an existing queue will have its tail restored to head if lost.
     */
    public function testCreateExistingQueueMissingTail()
    {
        $memcached = $this->getMock('Memcached', array('get', 'add'));

        $memcached->expects($this->exactly(3))
                  ->method('get')
                  ->will($this->returnCallback(function($key) {
                        if ($key == 'bernard_queuelist') return json_encode(array('send-newsletter'));
                        if ($key == 'bernard_send-newsletter_head') return 123;
                        if ($key == 'bernard_send-newsletter_tail') return false;
                    }))
        ;

        $memcached->expects($this->exactly(1))
                  ->method('add')
                  ->with('bernard_send-newsletter_tail', 123, 0)
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $driver->createQueue('send-newsletter');
    }

    /**
     * Test count messages works for tail - head
     */
    public function testCountMessages()
    {
        $memcached = $this->getMock('Memcached', array('get'));

        $memcached->expects($this->exactly(2))
                  ->method('get')
                  ->with($this->logicalOr('bernard_send-newsletter_head', 'bernard_send-newsletter_tail'))
                  ->will($this->returnCallback(function($key) {
                        if ($key == 'bernard_send-newsletter_head') return 99;
                        if ($key == 'bernard_send-newsletter_tail') return 123;
                    }))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $this->assertEquals($driver->countMessages('send-newsletter'), 123 - 99);
    }

    /**
     * Test that pushMessage correctly increments tail and pushes 
     * the message.
     */
    public function testPushMessageSuccess()
    {
        $memcached = $this->getMock('Memcached', array('increment', 'add'));

        $memcached->expects($this->exactly(1))
                  ->method('increment')
                  ->with('bernard_send-newsletter_tail')
                  ->will($this->returnValue(5123))
        ;

        $memcached->expects($this->exactly(1))
                  ->method('add')
                  ->with('bernard_send-newsletter_item_5122', 'TEST', 0)
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $driver->pushMessage('send-newsletter', 'TEST');
    }

    /**
     * Test that pushMessage correctly increments tail and when
     * failing to add message, decrementes the tail again.
     */
    public function testPushMessageFailAndDecrement()
    {
        $memcached = $this->getMock('Memcached', array('increment', 'add', 'decrement'));

        $memcached->expects($this->exactly(1))
                  ->method('increment')
                  ->with('bernard_send-newsletter_tail')
                  ->will($this->returnValue(5123))
        ;

        $memcached->expects($this->exactly(1))
                  ->method('add')
                  ->with('bernard_send-newsletter_item_5122', 'TEST', 0)
                  ->will($this->returnValue(false))
        ;

        $memcached->expects($this->exactly(1))
                  ->method('decrement')
                  ->with('bernard_send-newsletter_tail')
                  ->will($this->returnValue(5122))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');

        $exception = '';
        try {
            $driver->pushMessage('send-newsletter', 'TEST');
        } catch (DriverException $e) {
            $exception = $e->getMessage();
        }

        $this->assertEquals($exception, 'Unable to queue item: "bernard_send-newsletter_item_5122"');
    }

    /**
     * Test that popMessage for equal head and tail returns nothing
     */
    public function testPopMessageEmptyQueue()
    {
        $memcached = $this->getMock('Memcached', array('get', 'cas'));

        $memcached->expects($this->exactly(2))
                  ->method('get')
                  ->with($this->logicalOr('bernard_send-newsletter_head', 'bernard_send-newsletter_tail'))
                  ->will($this->returnCallback(function($key) {
                        if ($key == 'bernard_send-newsletter_head') return 123;
                        if ($key == 'bernard_send-newsletter_tail') return 123;
                    }))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard');
        $message = $driver->popMessage('send-newsletter');

        $this->assertEquals($message, null);
    }

    /**
     * Test that popMessage on non empty queue will use cas 
     * to update head after retreiving the message, and 
     * subsequently delete the message.
     */
    public function testPopMessageSuccess()
    {
        $memcached = $this->getMock('Memcached', array('get', 'cas', 'delete'));

        $memcached->expects($this->at(0))
                  ->method('get')
                  ->with('bernard_send-newsletter_head')
                  ->will($this->returnCallback(function($key, $cache_cb, &$casToken) {
                        $casToken = 1234.1234;
                        return 55;
                    }))
        ;

        $memcached->expects($this->at(1))
                  ->method('get')
                  ->with('bernard_send-newsletter_tail')
                  ->will($this->returnValue(99))
        ;

        $memcached->expects($this->at(2))
                  ->method('get')
                  ->with('bernard_send-newsletter_item_55')
                  ->will($this->returnValue('TEST'))
        ;
        
        $memcached->expects($this->at(3))
                  ->method('cas')
                  ->with(1234.1234, 'bernard_send-newsletter_head', 56)
                  ->will($this->returnValue(true))
        ;

        $memcached->expects($this->at(4))
                  ->method('delete')
                  ->with('bernard_send-newsletter_item_55')
                  ->will($this->returnValue(true))
        ;


        $driver = new MemcachedDriver($memcached, 'bernard');
        $message = $driver->popMessage('send-newsletter');

        $this->assertEquals($message, array('TEST', 'bernard_send-newsletter_item_55'));
    }

    /**
     * Test that popMessage will attempt to retreive a message
     * from a non empty queue, but give up and skip the message
     * after timeout.
     */
    public function testPopMessageFailedPushAndTimeoutOnThreshold()
    {
        $memcached = $this->getMock('Memcached', array('get', 'cas', 'delete'));

        $memcached->expects($this->any())
                  ->method('get')
                  ->with($this->logicalOr('bernard_send-newsletter_head', 'bernard_send-newsletter_tail', 'bernard_send-newsletter_item_99'))
                  ->will($this->returnCallback(function($key, $cache_cb, &$casToken) {
                        $casToken = 1234.1234;
                        if ($key == 'bernard_send-newsletter_head') return 99;
                        if ($key == 'bernard_send-newsletter_tail') return 123;
                        if ($key == 'bernard_send-newsletter_item_99') return false;
                    }))
        ;

        $memcached->expects($this->once())
                  ->method('cas')
                  ->with(1234.1234, 'bernard_send-newsletter_head', 100)
                  ->will($this->returnValue(true))
        ;

        $memcached->expects($this->never())
                  ->method('delete')
        ;

        $driver = new MemcachedDriver($memcached, 'bernard', 0.1);
        $message = $driver->popMessage('send-newsletter');

        $this->assertEquals($message, null);
    }

    /** 
     * Test that removeQueue will only delete keys relevant to the
     * queue, and that the queue list is updated using cas.
     */
    public function testRemoveQueue()
    {
        $memcached = $this->getMock('Memcached', array('getAllKeys', 'deleteMulti', 'get', 'cas'));

        $memcached->expects($this->once())
                  ->method('getAllKeys')
                  ->will($this->returnValue(array(
                        'some-other-key',
                        'bernard_queuelist',
                        'bernard_some-other-queue_head',
                        'bernard_some-other-queue_tail',
                        'bernard_some-other-queue_item_1',
                        'bernard_send-newsletter_head',
                        'bernard_send-newsletter_tail',
                        'bernard_send-newsletter_item_1',
                        'bernard_send-newsletter_item_2',
                        'bernard_send-newsletter_item_3',
                        'bernard_send-newsletter_item_4',
                        'bernard_send-newsletter_item_5',
                        'bernard_send-newsletter_item_6',
                        'some-other-key-01',
                        'some-other-key-02',
                    )))
        ;

        $memcached->expects($this->once())
                  ->method('deleteMulti')
                  ->with(array(
                        'bernard_send-newsletter_head',
                        'bernard_send-newsletter_tail',
                        'bernard_send-newsletter_item_1',
                        'bernard_send-newsletter_item_2',
                        'bernard_send-newsletter_item_3',
                        'bernard_send-newsletter_item_4',
                        'bernard_send-newsletter_item_5',
                        'bernard_send-newsletter_item_6',
                    ))
                  ->will($this->returnValue(true))
        ;

        $memcached->expects($this->once())
                  ->method('get')
                  ->with('bernard_queuelist')
                  ->will($this->returnCallback(function($key, $cache_cb, &$casToken) {
                        $casToken = 1234.1234;
                        return json_encode(array('some-other-queue', 'send-newsletter'));
                    }))
        ;

        $memcached->expects($this->once())
                  ->method('cas')
                  ->with(1234.1234, 'bernard_queuelist', json_encode(array('some-other-queue')))
                  ->will($this->returnValue(true))
        ;

        $driver = new MemcachedDriver($memcached, 'bernard', 0.1);
        $driver->removeQueue('send-newsletter');
    }
}
