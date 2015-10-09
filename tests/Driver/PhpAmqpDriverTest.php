<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\PhpAmqpDriver;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class PhpAmqpDriverTest extends \PHPUnit_Framework_TestCase
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
        $this->phpAmqpChannel = $this->getMock(
            '\PhpAmqpLib\Channel\AMQPChannel',
            array(
                'basic_publish',
                'basic_get',
                'basic_ack',
                'exchange_declare',
                'queue_declare',
                'queue_bind'
            ),
            array(),
            '',
            false
        );

        $this->phpAmqpConnection = $this->getMock(
            '\PhpAmqpLib\Connection\AMQPStreamConnection',
            array('channel'),
            array(),
            '',
            false
        );

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
            ->with('foo-queue', self::EXCHANGE_NAME)
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
                self::EXCHANGE_NAME
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
                self::EXCHANGE_NAME
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
        $this->phpAmqpChannel
            ->expects($this->once())
            ->method('basic_get')
            ->with($this->equalTo('foo-queue'))
            ->willReturn(null);

        $this->assertEquals([null, null], $this->driver->popMessage('foo-queue'));
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
