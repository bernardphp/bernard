<?php

namespace Bernard\Tests\Fixtures;

class Service
{
    static $onImportUsers = false;

    public function onFailSendNewsletter()
    {
        throw new \Exception();
    }

    public function onImportUsers()
    {
        static::$onImportUsers = true;
    }
}
