<?php

namespace Bernard\Doctrine;

use Doctrine\DBAL\Schema\Table;

/**
 * @package Bernard
 */
class MessagesSchema
{
    /**
     * Returns a Table instance setup to save messages for the queue in the
     * database.
     *
     * @return Table
     */
    public function createTable()
    {
        $table = new Table('bernard_messages');
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
        $table->addIndex(array('queue'));
        $table->addIndex(array('queue', 'sentAt', 'visible'));

        return $table;
    }
}
