<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine;

use Doctrine\DBAL\DBALException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Inspired by Swarrots ConnectionProcessor (https://github.com/swarrot/swarrot/blob/master/src/Swarrot/Processor/Doctrine/ConnectionProcessor.php).
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class ConnectionListener implements EventSubscriberInterface
{
    /**
     * @var Connection[]
     */
    private $connections;

    public static function getSubscribedEvents()
    {
        return [
            'bernard.invoke' => 'onPing',
        ];
    }

    public function __construct($connections)
    {
        if (!\is_array($connections)) {
            $connections = [$connections];
        }

        $this->connections = $connections;
    }

    public function onPing(): void
    {
        foreach ($this->connections as $connection) {
            if (!$connection->isConnected()) {
                continue;
            }

            try {
                $connection->query($connection->getDatabasePlatform()->getDummySelectSQL());
            } catch (DBALException $e) {
                $connection->close(); // close timed out connections so that using them connects again
            }
        }
    }
}
