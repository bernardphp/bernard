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
class SqsDriver extends AbstractPrefetchDriver
{
    protected $sqs;
    protected $queueUrls;

    /**
     * @param SqsClient $client
     * @param array     $queueUrls
     * @param integer|null $prefetch
     */
    public function __construct(SqsClient $sqs, array $queueUrls = array(), $prefetch = null)
    {
        parent::__construct($prefetch);

        $this->sqs       = $sqs;
        $this->queueUrls = $queueUrls;
    }

    /**
     * {@inheritDoc}
     */
    public function countMessages($queueName)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $result = $this->sqs->getQueueAttributes(array(
            'QueueUrl'       => $queueUrl,
            'AttributeNames' => array(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES)
        ));

        return $result->get(QueueAttribute::APPROXIMATE_NUMBER_OF_MESSAGES) ?: 0;
    }

    /**
     * {@inheritDoc}
     */
    public function createQueue($queueName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function removeQueue($queueName)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function listQueues()
    {
        $result = $this->sqs->listQueues();

        if (!$queueUrls = $result->get('QueueUrls')) {
            return array_keys($this->queueUrls);
        }

        foreach ($queueUrls as $queueUrl) {
            if (false !== array_search($queueUrl, $this->queueUrls)) {
                continue;
            }

            $queueName = current(array_reverse(explode('/', $queueUrl)));
            $this->queueUrls[$queueName] = $queueUrl;
        }

        return array_keys($this->queueUrls);
    }

    /**
     * {@inheritDoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $this->sqs->sendMessage(array(
            'QueueUrl'    => $queueUrl,
            'MessageBody' => $message
        ));
    }

    /**
     * As it is costly to make a request for messages from SQS we get a couple instead of a single
     * theese are cached and returned before making a new call.
     *
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        if ($message = $this->cached($queueName)) {
            return $message;
        }

        $queueUrl = $this->resolveUrl($queueName);

        $result = $this->sqs->receiveMessage(array(
            'QueueUrl'            => $queueUrl,
            'MaxNumberOfMessages' => $this->prefetch,
            'WaitTimeSeconds'     => $interval
        ));

        if (!$result || !$messages = $result->get('Messages')) {
            return array(null, null);
        }

        foreach ($messages as $message) {
            $this->cache($queueName, array($message['Body'], $message['ReceiptHandle']));
        }

        return $this->cached($queueName);
    }

    /**
     * {@inheritDoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $this->sqs->deleteMessage(array(
            'QueueUrl'      => $queueUrl,
            'ReceiptHandle' => $receipt,
        ));
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
    protected function resolveUrl($queueName)
    {
        if (isset($this->queueUrls[$queueName])) {
            return $this->queueUrls[$queueName];
        }

        $result = $this->sqs->getQueueUrl(array(
            'QueueName' => $queueName,
        ));

        if ($result && $queueUrl = $result->get('QueueUrl')) {
            return $this->queueUrls[$queueName] = $queueUrl;
        }

        throw new \InvalidArgumentException('Queue "' . $queueName .'" cannot be resolved to an url.');
    }

}
