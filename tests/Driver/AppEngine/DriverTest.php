<?php

namespace Bernard\Tests\Driver\AppEngine;

use Bernard\Driver\AppEngine\Driver;
use google\appengine\api\taskqueue\PushTask;

class DriverTest extends \PHPUnit\Framework\TestCase
{
    /** @var Driver */
    private $driver;

    public static function setUpBeforeClass()
    {
        // Very ugly hack! But AppEngine SDK isn't available outside appengine
        // environment.
        class_alias('Bernard\Tests\Fixtures\PushTask', 'google\appengine\api\taskqueue\PushTask');
    }

    public function setUp()
    {
        $this->driver = new Driver([
            'send-newsletter' => '/url_endpoint',
        ]);
    }

    public function tearDown()
    {
        PushTask::$messages = [];
    }

    public function testItQueuesPushTask()
    {
        $this->driver->pushMessage('send-newsletter', 'message');

        $message = new PushTask('/url_endpoint', ['message' => 'message']);
        $this->assertEquals($message, PushTask::$messages['send-newsletter'][0]);
    }

    public function testItUsesDefaultEndpointWhenAliasArentThere()
    {
        $this->driver->pushMessage('import-users', 'message');
        $this->driver->pushMessage('calculate-reports', 'message');

        $messages = [
            new PushTask('/_ah/queue/import-users', ['message' => 'message']),
            new PushTask('/_ah/queue/calculate-reports', ['message' => 'message']),
        ];

        $this->assertEquals($messages[0], PushTask::$messages['import-users'][0]);
        $this->assertEquals($messages[1], PushTask::$messages['calculate-reports'][0]);
    }

    public function testListQueues()
    {
        $this->assertEquals(['/url_endpoint' => 'send-newsletter'], $this->driver->listQueues());
    }
}
