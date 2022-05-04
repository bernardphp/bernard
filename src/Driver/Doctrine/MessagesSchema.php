<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine;

use Doctrine\DBAL\Schema\Schema;

class MessagesSchema
{
    /**
     * Creates tables on the current schema given.
     */
    public static function create(Schema $schema): void
    {
        static::createQueueTable($schema);
        static::createMessagesTable($schema);
    }

    /**
     * Creates queue table on the current schema given.
     */
    protected static function createQueueTable(Schema $schema): void
    {
        $table = $schema->createTable('bernard_queues');
        $table->addColumn('name', 'string');
        $table->setPrimaryKey(['name']);
    }

    /**
     * Creates message table on the current schema given.
     */
    protected static function createMessagesTable(Schema $schema): void
    {
        $table = $schema->createTable('bernard_messages');
        $table->addColumn('id', 'integer', [
            'autoincrement' => true,
            'unsigned' => true,
            'notnull' => true,
        ]);

        $table->addColumn('queue', 'string');
        $table->addColumn('message', 'text');
        $table->addColumn('visible', 'boolean', ['default' => true]);
        $table->addColumn('sentAt', 'datetime');
        $table->setPrimaryKey(['id']);
        $table->addIndex(['queue', 'sentAt', 'visible']);
    }
}
