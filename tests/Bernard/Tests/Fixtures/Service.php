<?php

namespace Bernard\Tests\Fixtures;

class Service
{
    public static $onImportUsers = false;

    public function onFailSendNewsletter()
    {
        throw new \Exception();
    }

    public function onImportUsers()
    {
        static::$onImportUsers = true;
    }

    public function onCreateFile()
    {
        touch(__DIR__ . '/create_file.test');
    }
}
