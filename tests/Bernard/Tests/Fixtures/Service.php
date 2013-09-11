<?php

namespace Bernard\Tests\Fixtures;

class Service
{
    public static $importUsers = false;

    public function failSendNewsletter()
    {
        throw new \Exception();
    }

    public function importUsers()
    {
        static::$importUsers = true;
    }

    public function createFile()
    {
        touch(__DIR__ . '/create_file.test');
    }
}
