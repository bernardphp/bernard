<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\MongoDB;

use Bernard\Driver\MongoDB\Driver;
use MongoClient;
use MongoCollection;
use MongoConnectionException;

/**
 * @coversDefaultClass \Bernard\Driver\MongoDB\Driver
 * @group functional
 */
class DriverFunctionalTest extends \PHPUnit\Framework\TestCase
{
    public const DATABASE = 'bernardQueueTest';
    public const MESSAGES = 'bernardMessages';
    public const QUEUES = 'bernardQueues';

    /** @var MongoCollection */
    private $messages;

    /** @var MongoCollection */
    private $queues;

    /** @var Driver */
    private $driver;

    protected function setUp(): void
    {
        if (!class_exists('MongoClient')) {
            $this->markTestSkipped('MongoDB extension is not available.');
        }

        try {
            $mongoClient = new MongoClient();
        } catch (MongoConnectionException $e) {
            $this->markTestSkipped('Cannot connect to MongoDB server.');
        }

        $this->queues = $mongoClient->selectCollection(self::DATABASE, self::QUEUES);
        $this->messages = $mongoClient->selectCollection(self::DATABASE, self::MESSAGES);
        $this->driver = new Driver($this->queues, $this->messages);
    }

    protected function tearDown(): void
    {
        if (!$this->messages instanceof MongoCollection) {
            return;
        }

        $this->messages->drop();
        $this->queues->drop();
    }

    /**
     * @medium
     * @covers ::acknowledgeMessage()
     * @covers ::countMessages()
     * @covers ::popMessage()
     * @covers ::pushMessage()
     */
    public function testMessageLifecycle(): void
    {
        $this->assertEquals(0, $this->driver->countMessages('foo'));

        $this->driver->pushMessage('foo', 'message1');
        $this->assertEquals(1, $this->driver->countMessages('foo'));

        $this->driver->pushMessage('foo', 'message2');
        $this->assertEquals(2, $this->driver->countMessages('foo'));

        [$message1, $receipt1] = $this->driver->popMessage('foo');
        $this->assertSame('message1', $message1, 'The first message pushed is popped first');
        $this->assertRegExp('/^[a-f\d]{24}$/i', $receipt1, 'The message receipt is an ObjectId');
        $this->assertEquals(1, $this->driver->countMessages('foo'));

        [$message2, $receipt2] = $this->driver->popMessage('foo');
        $this->assertSame('message2', $message2, 'The second message pushed is popped second');
        $this->assertRegExp('/^[a-f\d]{24}$/i', $receipt2, 'The message receipt is an ObjectId');
        $this->assertEquals(0, $this->driver->countMessages('foo'));

        [$message3, $receipt3] = $this->driver->popMessage('foo', 1);
        $this->assertNull($message3, 'Null message is returned when popping an empty queue');
        $this->assertNull($receipt3, 'Null receipt is returned when popping an empty queue');

        $this->assertEquals(2, $this->messages->count(), 'Popped messages remain in the database');

        $this->driver->acknowledgeMessage('foo', $receipt1);
        $this->assertEquals(1, $this->messages->count(), 'Acknowledged messages are removed from the database');

        $this->driver->acknowledgeMessage('foo', $receipt2);
        $this->assertEquals(0, $this->messages->count(), 'Acknowledged messages are removed from the database');
    }

    public function testPeekQueue(): void
    {
        $this->driver->pushMessage('foo', 'message1');
        $this->driver->pushMessage('foo', 'message2');

        $this->assertSame(['message1', 'message2'], $this->driver->peekQueue('foo'));
        $this->assertSame(['message2'], $this->driver->peekQueue('foo', 1));
        $this->assertSame([], $this->driver->peekQueue('foo', 2));
        $this->assertSame(['message1'], $this->driver->peekQueue('foo', 0, 1));
        $this->assertSame(['message2'], $this->driver->peekQueue('foo', 1, 1));
    }

    /**
     * @covers ::createQueue()
     * @covers ::listQueues()
     * @covers ::removeQueue()
     */
    public function testQueueLifecycle(): void
    {
        $this->driver->createQueue('foo');
        $this->driver->createQueue('bar');

        $queues = $this->driver->listQueues();
        $this->assertCount(2, $queues);
        $this->assertContains('foo', $queues);
        $this->assertContains('bar', $queues);

        $this->driver->removeQueue('foo');

        $queues = $this->driver->listQueues();
        $this->assertCount(1, $queues);
        $this->assertNotContains('foo', $queues);
        $this->assertContains('bar', $queues);
    }

    public function testRemoveQueueDeletesMessages(): void
    {
        $this->driver->pushMessage('foo', 'message1');
        $this->driver->pushMessage('foo', 'message2');
        $this->assertEquals(2, $this->driver->countMessages('foo'));
        $this->assertEquals(2, $this->messages->count());

        $this->driver->removeQueue('foo');
        $this->assertEquals(0, $this->driver->countMessages('foo'));
        $this->assertEquals(0, $this->messages->count());
    }

    public function testCreateQueueWithDuplicateNameIsNoop(): void
    {
        $this->driver->createQueue('foo');
        $this->driver->createQueue('foo');

        $this->assertSame(['foo'], $this->driver->listQueues());
    }

    public function testInfo(): void
    {
        $info = [
            'messages' => self::DATABASE.'.'.self::MESSAGES,
            'queues' => self::DATABASE.'.'.self::QUEUES,
        ];

        $this->assertSame($info, $this->driver->info());
    }
}
