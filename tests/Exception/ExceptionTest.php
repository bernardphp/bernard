<?php

namespace Bernard\Tests\Exception;

use Bernard\Exception;

class ExceptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider exceptionProvider
     */
    public function testThrowException($exception, $base)
    {
        try {
            throw new $exception();
        } catch (Exception $e) {
            $this->assertInstanceOf(Exception::class, $e);
            $this->assertInstanceOf($exception, $e);
            $this->assertInstanceOf($base, $e);

            return;
        }

        $this->fail('Exception not caught');
    }

    public function exceptionProvider()
    {
        return [
            ['\Bernard\Exception\QueueNotFoundException', '\RuntimeException'],
            ['\Bernard\Exception\ReceiverNotFoundException', '\RuntimeException'],
            ['\Bernard\Exception\ServiceUnavailableException', '\RuntimeException'],
        ];
    }
}
