<?php

namespace Bernard\Command\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

/**
 * @package Bernard
 */
class CreateCommand extends AbstractCommand
{
    protected $name = 'create';

    /**
     * {@inheritDoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getCreateSchema($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema)
    {
        $sync->createSchema($schema);
    }
}
