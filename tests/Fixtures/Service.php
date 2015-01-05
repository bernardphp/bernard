<?php

namespace Bernard\Tests\Fixtures;

use Bernard\Consumer;

class Service
{
    public static $importUsers = false;

    protected $consumer;

    public function __construct(Consumer $consumer)
    {
        $this->consumer = $consumer;
    }

    public function failSendNewsletter()
    {
        throw new \Exception();
    }

    public function importUsers()
    {
        static::$importUsers = true;

        $this->consumer->shutdown();
    }

    public function createFile()
    {
        touch(__DIR__ . '/create_file.test');
    }
}
