<?php

namespace Bernard\Tests\Driver;

use Aws\Sqs\SqsClient;
use Bernard\Driver\SqsDriver;
use Guzzle\Service\Resource\Model;

class SqsDriverTest extends \PHPUnit_Framework_TestCase
{
    const DUMMY_QUEUE_NAME       = 'my-queue';
    const DUMMY_QUEUE_URL_PREFIX = 'https://sqs.eu-west-1.amazonaws.com/123123';

    public function setUp()
    {
        $this->sqs = $this->getMockBuilder('Aws\Sqs\SqsClient')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getQueueUrl',
                'getQueueAttributes',
                'listQueues',
                'sendMessage',
                'receiveMessage',
                'deleteMessage',
            ))
            ->getMock();

        $this->driver = new SqsDriver($this->sqs, array('send-newsletter' => 'url'));
    }

    public function testItExposesInfo()
    {
        $driver = new SqsDriver($this->sqs, array(), 10);

        $this->assertEquals(array('prefetch' => 10), $driver->info());
        $this->assertEquals(array('prefetch' => 2), $this->driver->info());
    }

    public function testItImplementsDriverInterface()
    {
        $this->assertInstanceOf('Bernard\Driver\AbstractPrefetchDriver', $this->driver);
    }

    public function testItCountsNumberOfMessagesInQueue()
    {
        $this->assertSqsQueueUrl();
        $this->sqs
            ->expects($this->once())
            ->method('getQueueAttributes')
            ->with($this->equalTo(array(
                'QueueUrl'       => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME,
                'AttributeNames' => array('ApproximateNumberOfMessages'),
            )))
            ->will($this->returnValue(
                new Model(array(
                    'Attributes' => array('ApproximateNumberOfMessages' => 4),
                ))
            ));

        $this->assertEquals(4, $this->driver->countMessages(self::DUMMY_QUEUE_NAME));
    }

    public function testUnresolveableQueueNameThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException', 'Queue "unknown" cannot be resolved to an url.');

        $this->driver->popMessage('unknown');
    }

    public function testItGetsAllQueues()
    {
        $driver = new SqsDriver($this->sqs, array(
            'import-users' => 'alreadyknowurl/import_users_prod'
        ));

        $this->sqs
            ->expects($this->once())
            ->method('listQueues')
            ->will($this->returnValue(new Model(array(
                'QueueUrls' => array(
                    'https://sqs.eu-west-1.amazonaws.com/123123/failed',
                    'https://sqs.eu-west-1.amazonaws.com/123123/queue1',
                    'alreadyknowurl/import_users_prod',
                )
            ))));

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

        $sqsMessages = new Model(array(
            'Messages' => array(
                array('Body' => 'message0', 'ReceiptHandle' => 'r0'),
                array('Body' => 'message1', 'ReceiptHandle' => 'r1'),
            ),
        ));

        $this->sqs->expects($this->once())->method('receiveMessage')
            ->with($this->equalTo($query))->will($this->returnValue($sqsMessages));

        $this->assertEquals(array('message0', 'r0'), $this->driver->popMessage('send-newsletter'));
        $this->assertEquals(array('message1', 'r1'), $this->driver->popMessage('send-newsletter'));
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
        $this->driver->pushMessage('my-queue', 'This is a message');
    }

    public function testItPopMessages()
    {
        $this->sqs
            ->expects($this->at(0))
            ->method('getQueueUrl')
            ->with($this->equalTo(array(
                'QueueName' => self::DUMMY_QUEUE_NAME. '0',
            )))
            ->will($this->returnValue(new Model(array('QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '0'))));
        $this->sqs
            ->expects($this->at(1))
            ->method('receiveMessage')
            ->with($this->equalTo(array(
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/'. self::DUMMY_QUEUE_NAME. '0',
                'MaxNumberOfMessages' => 2,
                'WaitTimeSeconds'     => 5,
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
            ->will($this->returnValue(new Model(array(
                'QueueUrl' => self::DUMMY_QUEUE_URL_PREFIX . '/my-queue1',
            ))));
        $this->sqs
            ->expects($this->at(3))
            ->method('receiveMessage')
            ->with($this->equalTo(array(
                'QueueUrl'            => self::DUMMY_QUEUE_URL_PREFIX. '/my-queue1',
                'MaxNumberOfMessages' => 2,
                'WaitTimeSeconds'     => 30,
            )))
            ->will($this->returnValue(new Model(array(
                'Messages' => array(
                    array('Body' => 'message1', 'ReceiptHandle' => 'r1')
                )
            ))));

        $this->assertEquals(array('message0', 'r0'), $this->driver->popMessage('my-queue0'));
        $this->assertEquals(array('message1', 'r1'), $this->driver->popMessage('my-queue1', 30));
        $this->assertEquals(array(null, null), $this->driver->popMessage('send-newsletter'));
    }

    public function testAcknowledgeMessage()
    {
        $this->sqs->expects($this->once())->method('deleteMessage')
            ->with($this->equalTo(array('QueueUrl' => 'url', 'ReceiptHandle' => 'r0')));

        $this->driver->acknowledgeMessage('send-newsletter', 'r0');
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
