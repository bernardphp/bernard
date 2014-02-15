<?php

namespace Bernard\EventDispatcher;

use Bernard\EventDispatcher;
use Bernard\Envelope;
use Bernard\Queue;
use Psr\Log\LoggerInterface;

class LoggerSubscriber implements EventSubscriber
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function onProduce(Envelope $envelope, Queue $queue)
    {
        $this->logger->info('[bernard] produced {envelope} onto {queue}.', array(
            'envelope' => $envelope,
            'queue' => $queueName,
        ));
    }

    public function onConsume(Envelope $envelope, Queue $queue)
    {
        $this->logger->info('[bernard] consuming {envelope}.', array(
            'envelope' => $envelope,
        ));
    }

    public function onException(Envelope $envelope, Queue $queue, \Exception $e)
    {
        $this->logger->error('[bernard] caught exception {exception} while processing {envelope}.', array(
            'envelope' => $envelope,
            'exception' => $exception,
        ));
    }

    public function subscribe(EventDispatcher $dispatcher)
    {
        $dispatcher->on('bernard.produce', array($this, 'onProduce'));
        $dispatcher->on('bernard.consume', array($this, 'onConsume'));
        $dispatcher->on('bernard.exception', array($this, 'onException'));
    }
}
