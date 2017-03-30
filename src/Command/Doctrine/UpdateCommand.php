<?php

namespace Bernard\Command\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

/**
 * @package Bernard
 */
class UpdateCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('update');
    }

    /**
     * {@inheritDoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getUpdateSchema($schema);
    }

    /**
     * {@inheritDoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema)
    {
        $sync->updateSchema($schema);
    }
}
