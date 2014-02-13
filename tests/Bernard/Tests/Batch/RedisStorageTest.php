<?php

namespace Bernard\Tests\Batch;

use Bernard\Batch;
use Bernard\Batch\RedisStorage;
use Bernard\Batch\Status;

class RedisStorageTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('Missing redis extension.');
        }

        $this->redis = $this->getMock('Redis');
        $this->storage = new RedisStorage($this->redis);
    }

    public function testStorage()
    {
        $this->assertInstanceOf('Bernard\Batch\Storage', $this->storage);
    }

    public function testFindReturnsBatch()
    {
        $this->redis->expects($this->once())->method('hmget')
            ->with('batch:my-id', array('total', 'failed', 'successful', 'description'))
            ->will($this->returnValue(array('failed' => 2, 'total' => 20, 'successful' => 10, 'description' => 'Info Here...')));

        $batch = $this->storage->find('my-id');

        $this->assertEquals('my-id', $batch->getName());
        $this->assertEquals('Info Here...', $batch->getDescription());

        $this->assertEquals(new Status(20, 2, 10), $batch->getStatus());
    }

    public function testIncrementThrowsExceptionWhenWrongType()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->storage->increment(new Batch(), 'denmark');
    }

    public function testRegister()
    {
        $storage = new RedisStorage($this->redis, 3600);

        $multi = $this->getMock('Redis', array('expire', 'zadd', 'incr', 'set', 'exec'));

        $this->redis->expects($this->once())->method('multi')
            ->will($this->returnValue($multi));

        $multi->expects($this->at(0))->method('zadd')
            ->with('batches', time() + 3600, 'my-name');

        $multi->expects($this->at(1))->method('incr')
            ->with('batch:my-name:total');

        $multi->expects($this->at(2))->method('expire')
            ->with('batch:my-name:total', 3600);

        $multi->expects($this->at(3))->method('expire')
            ->with('batch:my-name:failed', 3600);

        $multi->expects($this->at(4))->method('expire')
            ->with('batch:my-name:successful', 3600);

        $multi->expects($this->at(5))->method('exec');

        $storage->register('my-name');
    }

    /**
     * @dataProvider incrementProvider
     */
    public function testIncrement($type, $key)
    {
        $this->redis->expects($this->once())->method('incr')
            ->with($key);

        $batch = new Batch('my-id');

        $this->storage->increment($batch->getName(), $type);
    }

    public function incrementProvider()
    {
        return array(
            array('successful', 'batch:my-id:successful'),
            array('failed', 'batch:my-id:failed'),
        );
    }
}
