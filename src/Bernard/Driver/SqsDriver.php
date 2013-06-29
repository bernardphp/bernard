<?php

namespace Bernard\Driver;

use Aws\Sqs\SqsClient;
use Aws\Sqs\Enum\QueueAttribute;
use Bernard\Message\Envelope;
use SplQueue;

/**
 * Implements a Driver for use with AWS SQS client API:
 * http://docs.aws.amazon.com/aws-sdk-php-2/latest/class-Aws.Sqs.SqsClient.html
 *
 * @package Bernard
 */
class SqsDriver implements \Bernard\Driver
{
    protected $sqs;
    protected $queueUrls;
    protected $queueCreateAttribs;
    protected $messages;

    /**
     * @param Aws\Sqs\SqsClient $client
     * @param array             $queueCreateAttribs
     */
    public function __construct(SqsClient $sqs, $queueCreateAttribs = array())
    {
        $this->sqs                = $sqs;
        $this->queueCreateAttribs = $queueCreateAttribs;
        $this->queueUrls          = array();
        $this->messages           = new SplQueue;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        if ($queueUrl = $this->resolveKey($queueName)) {
            $result = $this->sqs->getQueueAttributes(array(
                'QueueUrl'       => $queueUrl,
                'AttributeNames' => array(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES)
            ));
            return $result->get(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES) ?: 0;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
        $attribs = $this->queueCreateAttribs
            ? array('Attributes' => $this->queueCreateAttribs)
            : array();
        $result = $this->sqs->createQueue(array_merge($attribs, array('QueueName' => $queueName)));
        $this->queueUrls[$queueName] = $result->get('QueueUrl');
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
        if ($queueUrl = $this->resolveKey($queueName)) {
            unset($this->queueUrls[$queueName]);
            $this->sqs->deleteQueue(array('QueueUrl' => $queueUrl));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        $result = $this->sqs->listQueues();
        if ($queueUrls = $result->get('QueueUrls')) {
            return array_map(function ($url) {
                return preg_replace('~^.+/~', '', $url);
            }, $queueUrls);
        }
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        if ($queueUrl = $this->resolveKey($queueName)) {
            $this->sqs->sendMessage(array(
                'QueueUrl'    => $queueUrl,
                'MessageBody' => $message
            ));
        }
    }

    /**
     * As it is costly to make a request for messages from SQS we get a couple instead of a single
     * theese are cached and returned before making a new call.
     *
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        if (!$queueUrl = $this->resolveKey($queueName)) {
            return array(null, null);
        }

        if ($this->messages->count()) {
            return $this->messages->dequeue();
        }

        $result = $this->sqs->receiveMessage(array(
            'QueueUrl'            => $queueUrl,
            'MaxNumberOfMessages' => 4,
            'WaitTimeSeconds'     => $interval
        ));

        if (!$messages = $result->get('Messages')) {
            return array(null, null);
        }

        foreach ($messages as $message) {
            $this->messages->enqueue(array($message['Body'], $message['ReceiptHandle']));
        }

        return $this->messages->dequeue();
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        if ($queueUrl = $this->resolveKey($queueName)) {
            $this->sqs->deleteMessage(array(
                'QueueUrl'      => $queueUrl,
                'ReceiptHandle' => $receipt
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        // info per queue would be possible.. return info about all queues here?
        return null;
    }

    /**
     * AWS works with queue URLs rather than queue names. Returns either queue URL (if queue exists) for given name or null if not.
     *
     * @param  string $queueName
     * @return mixed
     */
    protected function resolveKey($queueName)
    {
        if (isset($this->queueUrls[$queueName])) {
            return $this->queueUrls[$queueName];
        }
        $result = $this->sqs->getQueueUrl(array('QueueName' => $queueName));
        if ($queueUrl = $result->get('QueueUrl')) {
            $this->queueUrls[$queueName] = $queueUrl;
        }
        return isset($this->queueUrls[$queueName]) ? $this->queueUrls[$queueName] : null;
    }

}
