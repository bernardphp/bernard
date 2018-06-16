<?php

namespace Bernard\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

/**
 * @group functional
 */
class SQLiteDriverTest extends AbstractDriverTest
{
    protected function isSupported()
    {
        return in_array('sqlite', \PDO::getAvailableDrivers());
    }

    public function testInfo()
    {
        $params = [
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ];

        $this->assertEquals($params, $this->driver->info());
    }

    protected function createConnection()
    {
        return DriverManager::getConnection([
            'memory' => true,
            'driver' => 'pdo_sqlite',
        ]);
    }
}
