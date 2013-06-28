<?php

namespace Bernard\Driver;

use Aws\Sqs\SqsClient;
use Aws\Sqs\Enum\QueueAttribute;
use Bernard\Message\Envelope;

/**
 * Implements a Driver for use with AWS SQS client API: http://docs.aws.amazon.com/aws-sdk-php-2/latest/class-Aws.Sqs.SqsClient.html
 *
 * @package Bernard
 */
class SqsDriver implements \Bernard\Driver
{
    protected $sqs;
    protected $queueUrls;
    protected $queueCreateAttribs;

    /**
     * @param Aws\Sqs\SqsClient $client
     * @param array             $queueCreateAttribs
     */
    public function __construct(SqsClient $sqs, $queueCreateAttribs = array())
    {
        $this->sqs                = $sqs;
        $this->queueCreateAttribs = $queueCreateAttribs;
        $this->queueUrls          = array();
        $this->openReceiptHandles = array();
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        if ($queueUrl = $this->queueNameToUrl($queueName)) {
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
        if ($queueUrl = $this->queueNameToUrl($queueName)) {
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
        if ($queueUrl = $this->queueNameToUrl($queueName)) {
            $this->sqs->sendMessage(array(
                'QueueUrl'    => $queueUrl,
                'MessageBody' => $message
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        if ($queueUrl = $this->queueNameToUrl($queueName)) {
            $result = $this->sqs->receiveMessage(array(
                'QueueUrl'            => $queueUrl,
                'MaxNumberOfMessages' => 1,
                'WaitTimeSeconds'     => $interval
            ));
            if ($messages = $result->get('Messages')) {
                foreach ($messages as $message) {
                    return array($message['Body'], $message['ReceiptHandle']);
                }
            }
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        if ($queueUrl = $this->queueNameToUrl($queueName)) {
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
    protected function queueNameToUrl($queueName)
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
