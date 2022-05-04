<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine\Command;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

class DropCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('drop');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getDropSchema($schema);
    }

    /**
     * {@inheritdoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema): void
    {
        $sync->dropSchema($schema);
    }
}
