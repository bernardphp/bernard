<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\InMemory;

use Bernard\Driver\InMemory\Driver;

/**
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
final class DriverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Driver
     */
    private $driver;

    protected function setUp(): void
    {
        $this->driver = new Driver();
    }

    /**
     *@test
     */
    public function testItListsQueues(): void
    {
        $this->driver->createQueue('queue1');
        $this->driver->createQueue('queue2');

        $this->assertEquals(['queue1', 'queue2'], $this->driver->listQueues());
    }
}
