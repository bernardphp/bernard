<?php

namespace Bernard\Tests\Driver;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Bernard\Driver\SqsDriver;
use Guzzle\Service\Resource\Model;

class SqsDriverTest extends \PHPUnit\Framework\TestCase
{
    const DUMMY_QUEUE_NAME       = 'my-queue';
    const DUMMY_FIFO_QUEUE_NAME  = 'my-queue.fifo';
    const DUMMY_QUEUE_URL_PREFIX = 'https://sqs.eu-west-1.amazonaws.com/123123';

    public function setUp()
    {
        $this->sqs = $this->getMockBuilder('Aws\Sqs\SqsClient')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'createQueue',
                'deleteQueue',
                'getQueueUrl',
                'getQueueAttributes',
                'listQueues',
                'sendMessage',
                'receiveMessage',
                'deleteMessage',
            ))
            ->getMock();

        $this->driver = new SqsDriver($this->sqs, ['send-newsletter' => 'url']);
    }

    public function testItExposesInfo()
    {
        $driver = new SqsDriver($this->sqs, [], 10);

        $this->assertEquals(['prefetch' => 10], $driver->info());
        $this->assertEquals(['prefetch' => 2], $this->driver->info());
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver\AbstractPrefetchDriver', $this->driver);
    }

    public function testItCreatesQueue()
    {
        $this->sqs
            ->expects($this->once())
            ->method('createQueue')
            ->with($this->equalTo(['QueueName' => self::DUMMY_QUEUE_NAME]))
            ->will($this->returnValue(
                $this->wrapResult([
                    'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX,
                ])
            ));

        // Calling this twice asserts that if queue exists
        // there won't be attempt to create it.
        $this->driver->createQueue(self::DUMMY_QUEUE_NAME);
        $this->driver->createQueue(self::DUMMY_QUEUE_NAME);
    }

    public function testItCreatesFifoQueue()
    {
        $this->sqs
            ->expects($this->once())
            ->method('createQueue')
            ->with($this->equalTo([
                'QueueName' => self::DUMMY_FIFO_QUEUE_NAME,
                'Attributes' => [
                    'FifoQueue' => 'true',
                ]
            ]))
            ->will($this->returnValue(
                $this->wrapResult([
                    'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX,
                ])
            ));

        // Calling this twice asserts that if queue exists
        // there won't be attempt to create it.
        $this->driver->createQueue(self::DUMMY_FIFO_QUEUE_NAME);
        $this->driver->createQueue(self::DUMMY_FIFO_QUEUE_NAME);
    }

    public function testItDeletesQueue()
    {
        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('deleteQueue')
            ->with($this->equalTo(['QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME]));

        $this->driver->removeQueue(self::DUMMY_QUEUE_NAME);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('getQueueAttributes')
            ->with($this->equalTo([
                'QueueUrl'       => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME,
                'AttributeNames' => array('ApproximateNumberOfMessages'),
            ]))
            ->will($this->returnValue(
                $this->wrapResult([
                    'Attributes' => ['ApproximateNumberOfMessages' => 4],
                ])
            ));

        $this->assertEquals(4, $this->driver->countMessages(self::DUMMY_QUEUE_NAME));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Queue "unknown" cannot be resolved to an url.
     */
    public function testUnresolveableQueueNameThrowsException()
    {
        $this->driver->popMessage('unknown');
    }

    public function testItGetsAllQueues()
    {
        $driver = new SqsDriver($this->sqs, [
            'import-users' => 'alreadyknowurl/import_users_prod'
        ]);

        $this->sqs
            ->expects($this->once())
            ->method('listQueues')
            ->will($this->returnValue($this->wrapResult([
                'QueueUrls' => [
                    'https://sqs.eu-west-1.amazonaws.com/123123/failed',
                    'https://sqs.eu-west-1.amazonaws.com/123123/queue1',
                    'alreadyknowurl/import_users_prod',
                ]
            ])));

        $queues = array('import-users', 'failed', 'queue1');

        $this->assertEquals($queues, $driver->listQueues());
    }

    public function testItPrefetchesMessages()
    {
        $query = array(
            'QueueUrl'            => 'url',
            'MaxNumberOfMessages' => 2,
            'WaitTimeSeconds'     => 5,
        );

        $sqsMessages = $this->wrapResult([
            'Messages' => [
                ['Body' => 'message0', 'ReceiptHandle' => 'r0'],
                ['Body' => 'message1', 'ReceiptHandle' => 'r1'],
            ],
        ]);

        $this->sqs->expects($this->once())->method('receiveMessage')
            ->with($this->equalTo($query))->will($this->returnValue($sqsMessages));

        $this->assertEquals(['message0', 'r0'], $this->driver->popMessage('send-newsletter'));
        $this->assertEquals(['message1', 'r1'], $this->driver->popMessage('send-newsletter'));
    }

    public function testItPushesMessages()
    {
        $message = 'This is a message';

        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->equalTo([
                'QueueUrl'    => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME,
                'MessageBody' => $message
            ]));
        $this->driver->pushMessage(self::DUMMY_QUEUE_NAME, $message);
    }

    public function testItPushesMessagesToFifoQueue()
    {
        $message = 'This is a message';

        $this->assertSqsFifoQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->equalTo([
                'QueueUrl'    => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_FIFO_QUEUE_NAME,
                'MessageBody' => $message,
                'MessageGroupId' => \Bernard\Driver\SqsDriver::class . '::pushMessage',
                'MessageDeduplicationId' => md5($message),

            ]));
        $this->driver->pushMessage(self::DUMMY_FIFO_QUEUE_NAME, $message);
    }

    public function testItPopMessages()
    {
        $this->sqs
            ->expects($this->at(0))
            ->method('getQueueUrl')
            ->with($this->equalTo([
                'QueueName' => self::DUMMY_QUEUE_NAME. '0',
            ]))
            ->will($this->returnValue($this->wrapResult([
                'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX
                    . '/'. self::DUMMY_QUEUE_NAME. '0',
            ])));
        $this->sqs
            ->expects($this->at(1))
            ->method('receiveMessage')
            ->with($this->equalTo([
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '0',
                'MaxNumberOfMessages' => 2,
                'WaitTimeSeconds'     => 5,
            ]))
            ->will($this->returnValue($this->wrapResult([
                'Messages' => [
                    ['Body' => 'message0', 'ReceiptHandle' => 'r0']
                ]
            ])));

        $this->sqs
            ->expects($this->at(2))
            ->method('getQueueUrl')
            ->with($this->equalTo([
                'QueueName' => self::DUMMY_QUEUE_NAME. '1'
            ]))
            ->will($this->returnValue($this->wrapResult([
                'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX . '/my-queue1',
            ])));
        $this->sqs
            ->expects($this->at(3))
            ->method('receiveMessage')
            ->with($this->equalTo([
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/my-queue1',
                'MaxNumberOfMessages' => 2,
                'WaitTimeSeconds'     => 30,
            ]))
            ->will($this->returnValue($this->wrapResult([
                'Messages' => [
                    ['Body' => 'message1', 'ReceiptHandle' => 'r1'],
                ],
            ])));

        $this->assertEquals(['message0', 'r0'], $this->driver->popMessage('my-queue0'));
        $this->assertEquals(['message1', 'r1'], $this->driver->popMessage('my-queue1', 30));
        $this->assertEquals([null, null], $this->driver->popMessage('send-newsletter'));
    }

    public function testAcknowledgeMessage()
    {
        $this->sqs->expects($this->once())->method('deleteMessage')
            ->with($this->equalTo([
                'QueueUrl' => 'url',
                'ReceiptHandle' => 'r0',
            ]));

        $this->driver->acknowledgeMessage('send-newsletter', 'r0');
    }

    private function assertSqsQueueUrl()
    {
        $this->sqs
            ->expects($this->once())
            ->method('getQueueUrl')
            ->with($this->equalTo(['QueueName' => self::DUMMY_QUEUE_NAME]))
            ->will($this->returnValue($this->wrapResult([
                'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX
                    . '/'. self::DUMMY_QUEUE_NAME,
            ])));
    }

    private function assertSqsFifoQueueUrl()
    {
        $this->sqs
            ->expects($this->once())
            ->method('getQueueUrl')
            ->with($this->equalTo(['QueueName' => self::DUMMY_FIFO_QUEUE_NAME]))
            ->will($this->returnValue($this->wrapResult([
                'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX
                    . '/'. self::DUMMY_FIFO_QUEUE_NAME,
            ])));
    }

    private function wrapResult($data = [])
    {
        return class_exists('Aws\Common\Aws')
            ? new Model($data)
            : new Result($data);
    }
}
