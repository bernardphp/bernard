<?php

namespace Bernard\Tests\Exception;

use PHPUnit_Framework_TestCase as TestCase;

class ExceptionTest extends TestCase
{
    /**
     * @dataProvider exceptionProvider
     */
    public function testThrowException($exception, $base)
    {
        try {
            throw new $exception;
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Bernard\Exception\Exception', $e);
            $this->assertInstanceOf($exception, $e);
            $this->assertInstanceOf($base, $e);
            return;
        }
        $this->fail("Exception not caught");
    }

    public function exceptionProvider()
    {
        return [
            ['\Bernard\Exception\InvalidArgumentException', '\InvalidArgumentException'],
            ['\Bernard\Exception\InvalidOperationException', '\Exception'],
            ['\Bernard\Exception\NotImplementedException', '\BadMethodCallException'],
            ['\Bernard\Exception\QueueNotFoundException', '\RuntimeException'],
            ['\Bernard\Exception\ReceiverNotFoundException', '\RuntimeException'],
            ['\Bernard\Exception\ServiceUnavailableException', '\RuntimeException'],
        ];
    }
}
