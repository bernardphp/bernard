<?php

namespace Bernard\Tests;

use Bernard\Builder;
use Bernard\Driver;

class ConsumerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Builder
     */
    protected $builder;

    public function setUp()
    {
        $this->driver = $this->getMock('Bernard\Driver');
        $this->builder = new Builder($this->driver);
    }

    public function testCreateWithoutDriver()
    {
        $builder = new Builder();
        $this->assertNull($builder->getDriver(), 'No driver set');
    }

    public function testCreateWithDriver()
    {
        $this->assertInstanceOf('Bernard\Driver', $this->builder->getDriver());
    }

    public function testDefaultServiceResolverInserted()
    {
        $this->assertInstanceOf('Bernard\ServiceResolver', $this->builder->getResolver());
    }

    public function testDefaultSerializerInserted()
    {
        $this->assertInstanceOf('Bernard\Serializer', $this->builder->getSerializer());
    }

    public function testEmptyServicesOnCreate()
    {
        $this->assertEmpty($this->builder->getServices(), 'No services on create');
    }

    public function testQueueFactoryCreatedWhenDriverGiven()
    {
        $this->assertInstanceOf('Bernard\QueueFactory', $this->builder->getQueueFactory());
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot create default queue factory without driver
     */
    public function testExceptionOnQueueFactoryWithoutDriver()
    {
        $builder = new Builder();
        $builder->getQueueFactory();
    }

    public function testServiceCanBeAdded()
    {
        $this->builder->setService('MessageName', 'Bernard\Tests\Fixtures\Service');
        $this->assertEquals(array('MessageName' => 'Bernard\Tests\Fixtures\Service'), $this->builder->getServices());
    }

    public function testCreateConsumer()
    {
        $consumer = $this->builder->createConsumer(array('MessageName' => 'Bernard\Tests\Fixtures\Service'));
        $this->assertEquals(array('MessageName' => 'Bernard\Tests\Fixtures\Service'), $this->builder->getServices());
        $this->assertInstanceOf('Bernard\Consumer', $consumer);
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage Cannot create consumer without services
     */
    public function testExceptionOnCreateConsumerWithoutServices()
    {
        $this->builder->createConsumer();
    }

    public function testCreateProducer()
    {
        $producer = $this->builder->createProducer();
        $this->assertInstanceOf('Bernard\Producer', $producer);
    }

    public function testSettersCanBeChained()
    {

        $serializer = $this->getMock('Bernard\Serializer');
        $resolver   = $this->getMock('Bernard\ServiceResolver');
        $factory    = $this->getMockBuilder('Bernard\QueueFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $check = $this->builder
            ->setDriver($this->driver)
            ->setSerializer($serializer)
            ->setResolver($resolver)
            ->setServices(array('MessageName1' => 'Bernard\Tests\Fixtures\Service'))
            ->setService('MessageName2', 'Bernard\Tests\Fixtures\Service')
            ->setQueueFactory($factory);
        $this->assertInstanceOf('Bernard\Builder', $check);
        $this->assertSame($this->builder, $check);
    }

}