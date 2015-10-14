<?php

namespace Bernard\Tests\Fixtures;

class Service
{
    public $importUsers = false;

    public function failSendNewsletter()
    {
        throw new \Exception();
    }

    public function importUsers()
    {
        $this->importUsers = true;
    }

    public function createFile()
    {
        touch(__DIR__ . '/create_file.test');
    }
}
