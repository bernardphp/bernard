<?php

namespace Bernard\EventListener\Doctrine;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\DBAL\DBALException;

/**
 * Inspired by Swarrots ConnectionProcessor (https://github.com/swarrot/swarrot/blob/master/src/Swarrot/Processor/Doctrine/ConnectionProcessor.php)
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @package Bernard
 */
class PingEventListener implements EventSubscriberInterface
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
        if (!is_array($connections)) {
            $connections = [$connections];
        }

        $this->connections = $connections;
    }

    public function onPing()
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
