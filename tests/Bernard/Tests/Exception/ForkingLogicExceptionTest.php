<?php

namespace Bernard\Tests\Exception;

use Bernard\Exception\ForkingLogicException;

class ForkingLogicExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testItsALogicException()
    {
        $this->assertInstanceOf('LogicException', new ForkingLogicException('class'));
    }

    public function testItContainsInformationAboutException()
    {
        $exception = new ForkingLogicException('class', 'message', 'file', 52, 400);

        $this->assertEquals('class', $exception->getClass());
        $this->assertEquals('message', $exception->getMessage());
        $this->assertEquals('file', $exception->getFile());
        $this->assertEquals(52, $exception->getLine());
        $this->assertEquals(400, $exception->getCode());
    }
}
