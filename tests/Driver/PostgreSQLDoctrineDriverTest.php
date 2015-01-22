<?php

namespace Bernard\Tests\Driver;

use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * @group functional
 */
class PostgreSQLDoctrineDriverTest extends AbstractDoctrineDriverTest
{
    protected function isSupported()
    {
        return in_array('pgsql', \PDO::getAvailableDrivers());
    }

    public function testInfo()
    {
        $params = array(
            'driver'   => 'pdo_pgsql',
            'dbname'   => 'bernard_test',
        );

        $this->assertEquals($params, $this->driver->info());
    }

    protected function createConnection()
    {
        return DriverManager::getConnection(array(
            'driver'   => 'pdo_pgsql',
            'user'     => 'postgres',
            'dbname'   => 'bernard_test',
            'password' => '',
        ));
    }
}
