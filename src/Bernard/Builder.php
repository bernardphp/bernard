<?php

namespace Bernard;

use Bernard\Consumer;
use Bernard\Driver;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer;
use Bernard\Serializer\NaiveSerializer;
use Bernard\ServiceResolver;
use Bernard\ServiceResolver\ObjectResolver;


/**
 * Knows how to create consumers, producers, queue factories
 *
 * @package Bernard
 */
class Builder
{
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var ServiceResolver
     */
    protected $resolver;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var array
     */
    protected $services;


    public function __construct(Driver $driver = null)
    {
        if ($driver) {
            $this->setDriver($driver);
        }
        $this->services = array();
    }

    /**
     * Creates new Consumer object
     *
     * @param array $services optional services to be added
     *
     * @return Consumer
     *
     * @throws \RuntimeException
     */
    public function createConsumer(array $services = array())
    {
        foreach ($services as $name => $service) {
            $this->setService($name, $service);
        }

        if (!$this->services) {
            throw new \RuntimeException("Cannot create consumer without services");
        }
        $resolver = $this->getResolver();
        foreach ($this->getServices() as $name => $service) {
            $resolver->register($name, $service);
        }

        return new Consumer($resolver);
    }

    /**
     * Creates new Consumer object
     *
     * @return Consumer
     */
    public function createProducer()
    {
        return new Producer($this->getQueueFactory());
    }

    /**
     * @param Driver $driver
     */
    public function setDriver(Driver $driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @return Driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param QueueFactory $queues
     */
    public function setQueueFactory(QueueFactory $queues)
    {
        $this->queueFactory = $queues;

        return $this;
    }

    /**
     * @throw  \@RuntimeException
     * @return QueueFactory
     */
    public function getQueueFactory()
    {
        if (!$this->queueFactory) {
            if (!$driver = $this->getDriver()) {
                throw new \RuntimeException("Cannot create default queue factory without driver");
            }
            $this->setQueueFactory(new PersistentFactory($driver, $this->getSerializer()));
        }
        return $this->queueFactory;
    }

    /**
     * @param ServiceResolver $resolver
     */
    public function setResolver(ServiceResolver $resolver)
    {
        $this->resolver = $resolver;

        return $this;
    }

    /**
     * @return \Bernard\ServiceResolver
     */
    public function getResolver()
    {
        if (!$this->resolver) {
            $this->setResolver(new ObjectResolver);
        }
        return $this->resolver;
    }

    /**
     * @param   $serializer
     * @return Builder
     */
    public function setSerializer(Serializer $serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @return \Bernard\Serializer
     */
    public function getSerializer()
    {
        if (!$this->serializer) {
            $this->setSerializer(new NaiveSerializer);
        }
        return $this->serializer;
    }

    /**
     * @param array $services
     */
    public function setServices(array $services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @param string $name
     * @param mixed  $service
     */
    public function setService($name, $service)
    {
        $this->services[$name] = $service;

        return $this;
    }

    /**
     * @return array
     */
    public function getServices()
    {
        return $this->services;
    }


}