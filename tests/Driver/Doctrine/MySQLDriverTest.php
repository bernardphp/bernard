<?php

namespace Bernard\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

/**
 * @group functional
 */
class MySQLDriverTest extends AbstractDriverTest
{
    protected function isSupported()
    {
        return in_array('mysql', \PDO::getAvailableDrivers());
    }

    public function testInfo()
    {
        $params = array(
            'driver'   => 'pdo_mysql',
            'host'     => '127.0.0.1',
            'dbname'   => 'bernard_test',
        );

        $this->assertEquals($params, $this->driver->info());
    }

    protected function createConnection()
    {
        return DriverManager::getConnection(array(
            'driver'   => 'pdo_mysql',
            'host'     => '127.0.0.1',
            'user'     => 'root',
            'dbname'   => 'bernard_test',
            'password' => '',
        ));
    }
}
