<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine\Command;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;

class CreateCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct('create');
    }

    /**
     * {@inheritdoc}
     */
    protected function getSql(Synchronizer $sync, Schema $schema)
    {
        return $sync->getCreateSchema($schema);
    }

    /**
     * {@inheritdoc}
     */
    protected function applySql(Synchronizer $sync, Schema $schema): void
    {
        $sync->createSchema($schema);
    }
}
