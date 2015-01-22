<?php

namespace Bernard\Tests\Driver;

use Bernard\Doctrine\MessagesSchema;
use Bernard\Driver\DoctrineDriver;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;

/**
 * @group functional
 */
class MySQLDoctrineDriverTest extends AbstractDoctrineDriverTest
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
