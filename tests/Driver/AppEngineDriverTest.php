<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\AppEngineDriver;
use google\appengine\api\taskqueue\PushTask;

class AppEngineDriverTest extends \PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass()
    {
        // Very ugly hack! But AppEngine SDK isn't available outside appengine
        // environment.
        class_alias('Bernard\Tests\Fixtures\PushTask', 'google\appengine\api\taskqueue\PushTask');
    }

    public function setUp()
    {
        $this->driver = new AppEngineDriver(array(
            'send-newsletter' => '/url_endpoint',
        ));
    }

    public function tearDown()
    {
        PushTask::$messages = array();
    }

    public function testItQueuesPushTask()
    {
        $this->driver->pushMessage('send-newsletter', 'message');

        $message = new PushTask('/url_endpoint', array('message' => 'message'));
        $this->assertEquals($message, PushTask::$messages['send-newsletter'][0]);
    }

    public function testItUsesDefaultEndpointWhenAliasArentThere()
    {
        $this->driver->pushMessage('import-users', 'message');
        $this->driver->pushMessage('calculate-reports', 'message');

        $messages = array(
            new PushTask('/_ah/queue/import-users', array('message' => 'message')),
            new PushTask('/_ah/queue/calculate-reports', array('message' => 'message')),
        );

        $this->assertEquals($messages[0], PushTask::$messages['import-users'][0]);
        $this->assertEquals($messages[1], PushTask::$messages['calculate-reports'][0]);
    }

    public function testListQueues()
    {
        $this->assertEquals(array('/url_endpoint' => 'send-newsletter'), $this->driver->listQueues());
    }
}
