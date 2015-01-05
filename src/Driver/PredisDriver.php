<?php

namespace Bernard\Driver;

use Predis\ClientInterface;

/**
 * @package Bernard
 */
class PredisDriver extends PhpRedisDriver
{
    protected $redis;

    /**
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritDoc}
     */
    public function popMessage($queueName, $interval = 5)
    {
        list(, $message) = $this->redis->blpop($this->resolveKey($queueName), $interval) ?: null;

        return array($message, null);
    }

    /**
     * {@inheritDoc}
     */
    public function info()
    {
        // Temporarily change the command use to get info as earlier and newer redis
        // versions breaks it into sections.
        $commandClass = $this->redis->getProfile()->getCommandClass('info');
        $this->redis->getProfile()->defineCommand('info', 'Predis\Command\ServerInfo');

        $info = $this->redis->info();

        $this->redis->getProfile()->defineCommand('info', $commandClass);

        return $info;
    }
}
