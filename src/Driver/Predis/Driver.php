<?php

namespace Bernard\Driver\Predis;

use Predis\ClientInterface;

/**
 * @package Bernard
 */
final class Driver extends \Bernard\Driver\PhpRedis\Driver
{
    /**
     * @param ClientInterface $redis
     */
    public function __construct(ClientInterface $redis)
    {
        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    public function popMessage($queueName, $duration = 5)
    {
        list(, $message) = $this->redis->blpop($this->resolveKey($queueName), $duration) ?: null;

        return [$message, null];
    }

    /**
     * {@inheritdoc}
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
