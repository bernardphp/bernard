<?php

namespace Bernard\Tests\Driver;

use Bernard\Driver\AppEngineDriver;
use google\appengine\api\taskqueue\PushTask;

class AppEngineDriverTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        // Very ugly hack! But AppEngine SDK isnt available outside appengine
        // environment.
        class_alias('Bernard\Tests\Fixtures\PushTask', 'google\appengine\api\taskqueue\PushTask');
    }

    public function setUp()
    {
        $this->driver = new AppEngineDriver(array(
            'send-newsletter' => '/url_endpoint',
        ));
    }

    public function testItQueuesPushTask()
    {
        $this->driver->pushMessage('send-newsletter', 'message');

        $message = new PushTask('/url_endpoint', array('message' => 'message'));
        $this->assertEquals($message, PushTask::$messages['send-newsletter'][0]);
    }

    public function testThrowsExceptionOnInvalidQueueMap()
    {
        $this->setExpectedException('InvalidArgumentException', 'Queue "import-users" cannot be resolved to an endpoint.');

        $this->driver->pushMessage('import-users', '');
    }

    public function testListQueues()
    {
        $this->assertEquals(array('/url_endpoint' => 'send-newsletter'), $this->driver->listQueues());
    }
}
