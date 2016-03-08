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

    public function importReport(Report $report)
    {
        // note: the class hinted on this method does not exist on purpose, as calling this method should cause a
        //       Throwable to be thrown
    }
}
