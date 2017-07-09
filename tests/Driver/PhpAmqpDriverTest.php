<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PhpAmqpDriver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PhpAmqpDriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AMQPStreamConnection
     */
    private $phpAmqpConnection;

    /**
     * @var AMQPChannel
     */
    private $phpAmqpChannel;

    /**
     * @var PhpAmqpDriver
     */
    private $driver;

    const EXCHANGE_NAME = 'foo-exchange';

    protected function setUp()
    {
        $this->phpAmqpChannel = $this->getMockBuilder('\PhpAmqpLib\Channel\AMQPChannel')
            ->setMethods(array(
                'basic_publish',
                'basic_get',
                'basic_ack',
                'exchange_declare',
                'queue_declare',
                'queue_bind'
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->phpAmqpConnection = $this->getMockBuilder('\PhpAmqpLib\Connection\AMQPStreamConnection')
            ->setMethods(array(
                'channel',
            ))
            ->disableOriginalConstructor()
            ->getMock();

        $this->phpAmqpConnection
            ->expects($this->any())
            ->method('channel')
            ->willReturn($this->phpAmqpChannel);

        $this->driver = new PhpAmqpDriver($this->phpAmqpConnection, self::EXCHANGE_NAME);
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->driver);
    }

    public function testItCreatesQueue()
    {
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('exchange_declare')
            ->with(self::EXCHANGE_NAME, 'direct', false, true, false)
        ;
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('queue_declare')
            ->with('foo-queue', false, true, false, false)
        ;
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('queue_bind')
            ->with('foo-queue', self::EXCHANGE_NAME, 'foo-queue')
        ;

        $this->driver->createQueue('foo-queue');
    }

    public function testItPushesMessages()
    {
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function(AMQPMessage $message) {
                    return $message->body == 'dummy push message';
                }),
                self::EXCHANGE_NAME,
                'not-relevant'
            );
        $this->driver->pushMessage('not-relevant', 'dummy push message');
    }

    public function testItUsesDefaultParameters()
    {
        $this->driver = new PhpAmqpDriver(
            $this->phpAmqpConnection,
            self::EXCHANGE_NAME,
            array('delivery_mode' => 2)
        );

        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('basic_publish')
            ->with(
                $this->callback(function(AMQPMessage $message) {
                    return $message->get('delivery_mode') === 2;
                }),
                self::EXCHANGE_NAME,
                'not-relevant'
            );
        $this->driver->pushMessage('not-relevant', 'dummy push message');
    }

    public function testItPopsMessages()
    {
        $amqpMessage = new AMQPMessage('bar');
        $amqpMessage->delivery_info['delivery_tag'] = 'alright';

        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('basic_get')
            ->with($this->equalTo('foo-queue'))
            ->willReturn($amqpMessage);

        $this->assertEquals(['bar', 'alright'], $this->driver->popMessage('foo-queue'));
    }

    public function testItPopsArrayWithNullsWhenThereAreNoMessages()
    {
        $startTime = microtime(true);

        $this->phpAmqpChannel
            ->expects($this->any())
            ->method('basic_get')
            ->with($this->equalTo('foo-queue'))
            ->willReturn(null);

        $result = $this->driver->popMessage('foo-queue', 0.1);
        $duration = microtime(true) - $startTime;

        $this->assertEquals([null, null], $result);
        $this->assertGreaterThan(0.1, $duration);
    }

    public function testItAcknowledgesMessage()
    {
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('basic_ack')
            ->with('delivery-tag');

        $this->driver->acknowledgeMessage('irrelevant', 'delivery-tag');
    }
}
