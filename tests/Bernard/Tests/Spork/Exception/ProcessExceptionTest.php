<?php

namespace Bernard\Tests\Spork\Exception;

use Bernard\Spork\Exception\ProcessException;

class ProcessExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testItsALogicException()
    {
        $this->assertInstanceOf('LogicException', new ProcessException('class'));
    }

    public function testItContainsInformationAboutException()
    {
        $exception = new ProcessException('class', 'message', 'file', 52, 400);

        $this->assertEquals('class', $exception->getClass());
        $this->assertEquals('message', $exception->getMessage());
        $this->assertEquals('file', $exception->getFile());
        $this->assertEquals(52, $exception->getLine());
        $this->assertEquals(400, $exception->getCode());
    }
}
