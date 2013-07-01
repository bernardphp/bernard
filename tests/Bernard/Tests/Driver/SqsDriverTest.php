<?php

namespace Bernard\Tests\Driver;

use Aws\Sqs\SqsClient;
use Bernard\Driver\SqsDriver;
use Guzzle\Service\Resource\Model;
use Aws\Sqs\Enum\QueueAttribute;

class SqsDriverTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_QUEUE_NAME       = 'my-queue';
    const DUMMY_QUEUE_URL_PREFIX = 'https://sqs.eu-west-1.amazonaws.com/123123';

    public function setUp()
    {

        $this->sqs = $this->getMockBuilder('Aws\Sqs\SqsClient')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'resolveUrl',
                'getQueueUrl',
                'getQueueAttributes',
                'listQueues',
                'sendMessage',
                'receiveMessage'
            ))
            ->getMock();
        $this->connection = new SqsDriver($this->sqs);
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->connection);
    }


    public function testResolveQueueNameToUrl()
    {
        $this->assertSqsQueueUrl();
        $method = new \ReflectionMethod(get_class($this->connection), 'resolveUrl');
        $method->setAccessible(true);
        $this->assertEquals(self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME, $method->invoke($this->connection, self::DUMMY_QUEUE_NAME));
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('getQueueAttributes')
            ->with($this->equalTo(array(
                'QueueUrl'       => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME,
                'AttributeNames' => array(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES)
            )))
            ->will($this->returnValue(new Model(array(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES => 4))));
        $this->assertEquals(4, $this->connection->countMessages(self::DUMMY_QUEUE_NAME));
    }

    public function testItGetsAllKeys()
    {
        $this->sqs
            ->expects($this->once())
            ->method('listQueues')
            ->will($this->returnValue(new Model(array(
                'QueueUrls' => array(
                    'https://sqs.eu-west-1.amazonaws.com/123123/failed',
                    'https://sqs.eu-west-1.amazonaws.com/123123/queue1'
                )
            ))));
        $this->assertEquals(array('failed', 'queue1'), $this->connection->listQueues());
    }

    public function testItPushesMessages()
    {
        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->equalTo(array(
                'QueueUrl'    => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME,
                'MessageBody' => 'This is a message'
            )));
        $this->connection->pushMessage('my-queue', 'This is a message');
    }

    public function testItPopMessages()
    {
        $this->sqs
            ->expects($this->at(0))
            ->method('getQueueUrl')
            ->with($this->equalTo(array(
                'QueueName' => self::DUMMY_QUEUE_NAME. '0'
            )))
            ->will($this->returnValue(new Model(array('QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '0'))));
        $this->sqs
            ->expects($this->at(1))
            ->method('receiveMessage')
            ->with($this->equalTo(array(
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '0',
                'MaxNumberOfMessages' => SqsDriver::DEFAULT_PREFETCH_SIZE,
                'WaitTimeSeconds'     => SqsDriver::DEFAULT_WAIT_TIMEOUT
            )))
            ->will($this->returnValue(new Model(array(
                'Messages' => array(
                    array('Body' => 'message0', 'ReceiptHandle' => 'r0')
                )
            ))));

        $this->sqs
            ->expects($this->at(2))
            ->method('getQueueUrl')
            ->with($this->equalTo(array(
                'QueueName' => self::DUMMY_QUEUE_NAME. '1'
            )))
            ->will($this->returnValue(new Model(array('QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '1'))));
        $this->sqs
            ->expects($this->at(3))
            ->method('receiveMessage')
            ->with($this->equalTo(array(
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '1',
                'MaxNumberOfMessages' => SqsDriver::DEFAULT_PREFETCH_SIZE,
                'WaitTimeSeconds'     => 30
            )))
            ->will($this->returnValue(new Model(array(
                'Messages' => array(
                    array('Body' => 'message1', 'ReceiptHandle' => 'r1')
                )
            ))));

        $this->assertEquals(array('message0', 'r0'), $this->connection->popMessage(self::DUMMY_QUEUE_NAME. '0'));
        $this->assertEquals(array('message1', 'r1'), $this->connection->popMessage(self::DUMMY_QUEUE_NAME. '1', 30));
    }

    private function assertSqsQueueUrl()
    {
        $this->sqs
            ->expects($this->once())
            ->method('getQueueUrl')
            ->with($this->equalTo(array(
                'QueueName' => self::DUMMY_QUEUE_NAME
            )))
            ->will($this->returnValue(new Model(array('QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME))));
    }
}