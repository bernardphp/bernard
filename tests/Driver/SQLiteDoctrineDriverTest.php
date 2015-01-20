<?php

namespace Bernard\Tests\Driver;

use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

class SQLiteDoctrineDriverTest extends AbstractDoctrineDriverTest
{
    protected function isSupported()
    {
        return in_array('sqlite', \PDO::getAvailableDrivers());
    }

    public function testInfo()
    {
        $params = array(
            'memory'   => true,
            'driver'   => 'pdo_sqlite',
        );

        $this->assertEquals($params, $this->driver->info());
    }

    protected function createConnection()
    {
        return DriverManager::getConnection(array(
            'memory'   => true,
            'driver'   => 'pdo_sqlite',
        ));
    }
}
