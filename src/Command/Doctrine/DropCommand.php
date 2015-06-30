<?php

namespace Bernard\Command\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

/**
 * @package Bernard
 */
class DropCommand extends AbstractCommand
{
    protected $name = 'drop';

    /**
     * {@inheritDoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getDropSchema($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema)
    {
        $sync->dropSchema($schema);
    }
}
