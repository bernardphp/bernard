<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine\Command;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

class UpdateCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('update');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getUpdateSchema($schema);
    }

    /**
     * {@inheritdoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema): void
    {
        $sync->updateSchema($schema);
    }
}
