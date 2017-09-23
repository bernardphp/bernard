<?php

namespace Bernard\Driver;

use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;

/**
 * Implements a Driver for use with AWS SQS client API:
 * @link http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html
 *
 * @package Bernard
 */
class SqsDriver extends AbstractPrefetchDriver
{
    const AWS_SQS_FIFO_SUFFIX = '.fifo';
    const AWS_SQS_EXCEPTION_BAD_REQUEST = 400;

    protected $sqs;
    protected $queueUrls;

    /**
     * @param SqsClient $sqs
     * @param array     $queueUrls
     * @param int|null  $prefetch
     */
    public function __construct(SqsClient $sqs, array $queueUrls = [], $prefetch = null)
    {
        parent::__construct($prefetch);

        $this->sqs = $sqs;
        $this->queueUrls = $queueUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function listQueues()
    {
        $result = $this->sqs->listQueues();

        if (!$queueUrls = $result->get('QueueUrls')) {
            return array_keys($this->queueUrls);
        }

        foreach ($queueUrls as $queueUrl) {
            if (in_array($queueUrl, $this->queueUrls)) {
                continue;
            }

            $queueName = current(array_reverse(explode('/', $queueUrl)));
            $this->queueUrls[$queueName] = $queueUrl;
        }

        return array_keys($this->queueUrls);
    }

    /**
     * {@inheritdoc}
     *
     * @link http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#createqueue
     *
     * @return void
     *
     * @throws SqsException
     */
    public function createQueue($queueName)
    {
        if($this->queueExists($queueName)){
            return;
        }

        $parameters = [
            'QueueName' => $queueName,
        ];

        if($this->isFifoQueue($queueName)) {
            $parameters['Attributes'] = [
                'FifoQueue' => 'true',
            ];
        }

        $result = $this->sqs->createQueue($parameters);

        $this->queueUrls[$queueName] = $result['QueueUrl'];
    }

    /**
     * @param string $queueName
     *
     * @return bool
     *
     * @throws SqsException
     */
    private function queueExists($queueName)
    {
        try {
            $this->resolveUrl($queueName);
            return true;
        } catch (\InvalidArgumentException $exception) {
            return false;
        } catch (SqsException $exception) {
            if ($previousException = $exception->getPrevious()) {
                if ($previousException->getCode() === self::AWS_SQS_EXCEPTION_BAD_REQUEST) {
                    return false;
                }
            }

            throw $exception;
        }
    }

    /**
     * @param string $queueName
     *
     * @return bool
     */
    private function isFifoQueue($queueName)
    {
        return $this->endsWith($queueName, self::AWS_SQS_FIFO_SUFFIX);
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length === 0) {
            return true;
        }

        return (substr($haystack, -$length) === $needle);
    }

    /**
     * {@inheritdoc}
     */
    public function countMessages($queueName)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $result = $this->sqs->getQueueAttributes([
            'QueueUrl' => $queueUrl,
            'AttributeNames' => ['ApproximateNumberOfMessages'],
        ]);

        if (isset($result['Attributes']['ApproximateNumberOfMessages'])) {
            return (int)$result['Attributes']['ApproximateNumberOfMessages'];
        }

        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function pushMessage($queueName, $message)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $parameters = [
            'QueueUrl' => $queueUrl,
            'MessageBody' => $message,
        ];

        if($this->isFifoQueue($queueName)){
            $parameters['MessageGroupId'] = __METHOD__;
            $parameters['MessageDeduplicationId'] = md5($message);
        }

        $this->sqs->sendMessage($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        if ($message = $this->cache->pop($queueName)) {
            return $message;
        }

        $queueUrl = $this->resolveUrl($queueName);

        $result = $this->sqs->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $this->prefetch,
            'WaitTimeSeconds' => $duration,
        ]);

        if (!$result || !$messages = $result->get('Messages')) {
            return [null, null];
        }

        foreach ($messages as $message) {
            $this->cache->push($queueName, [$message['Body'], $message['ReceiptHandle']]);
        }

        return $this->cache->pop($queueName);
    }

    /**
     * {@inheritdoc}
     */
    public function acknowledgeMessage($queueName, $receipt)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $this->sqs->deleteMessage([
            'QueueUrl' => $queueUrl,
            'ReceiptHandle' => $receipt,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function peekQueue($queueName, $index = 0, $limit = 20)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @link http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-sqs-2012-11-05.html#deletequeue
     */
    public function removeQueue($queueName)
    {
        $queueUrl = $this->resolveUrl($queueName);

        $this->sqs->deleteQueue([
            'QueueUrl' => $queueUrl,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return [
            'prefetch' => $this->prefetch,
        ];
    }

    /**
     * AWS works with queue URLs rather than queue names. Returns either queue URL (if queue exists) for given name or null if not.
     *
     * @param string $queueName
     *
     * @return mixed
     *
     * @throws SqsException
     */
    protected function resolveUrl($queueName)
    {
        if (isset($this->queueUrls[$queueName])) {
            return $this->queueUrls[$queueName];
        }

        $result = $this->sqs->getQueueUrl(['QueueName' => $queueName]);

        if ($result && $queueUrl = $result->get('QueueUrl')) {
            return $this->queueUrls[$queueName] = $queueUrl;
        }

        throw new \InvalidArgumentException('Queue "' . $queueName .'" cannot be resolved to an url.');
    }
}
