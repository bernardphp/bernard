<?php

namespace Bernard\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

/**
 * @group functional
 */
class PostgreSQLDriverTest extends AbstractDriverTest
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
