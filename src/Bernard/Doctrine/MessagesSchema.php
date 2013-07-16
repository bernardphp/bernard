<?php

namespace Bernard\Doctrine;

use Doctrine\DBAL\Schema\Schema;

/**
 * @package Bernard
 */
class MessagesSchema
{
    /**
     * Creates tables on the current schema given.
     *
     * @param Schema $schema
     */
    public static function create(Schema $schema)
    {
        static::createQueueTable($schema);
        static::createMessagesTable($schema);
    }

    protected static function createQueueTable(Schema $schema)
    {
        $table = $schema->createTable('bernard_queues');
        $table->addColumn('name', 'string');
        $table->setPrimaryKey(array('name'));
    }

    protected static function createMessagesTable(Schema $schema)
    {
        $table = $schema->createTable('bernard_messages');
        $table->addColumn('id', 'integer', array(
            'autoincrement' => true,
            'unsigned'      => true,
            'notnull'       => true,
        ));

        $table->addColumn('queue', 'string');
        $table->addColumn('message', 'text');
        $table->addColumn('visible', 'boolean', array('default' => true));
        $table->addColumn('sentAt', 'datetime');
        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('queue', 'sentAt', 'visible'));
    }
}
