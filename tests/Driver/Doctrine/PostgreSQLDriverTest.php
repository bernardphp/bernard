<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine;

use Doctrine\DBAL\DriverManager;

/**
 * @group functional
 */
class PostgreSQLDriverTest extends AbstractDriverTest
{
    protected function isSupported()
    {
        return \in_array('pgsql', \PDO::getAvailableDrivers());
    }

    public function testInfo(): void
    {
        $params = [
            'driver' => 'pdo_pgsql',
            'dbname' => 'bernard_test',
        ];

        $this->assertEquals($params, $this->driver->info());
    }

    protected function createConnection()
    {
        return DriverManager::getConnection([
            'driver' => 'pdo_pgsql',
            'user' => 'postgres',
            'dbname' => 'bernard_test',
            'password' => '',
        ]);
    }
}
