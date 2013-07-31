<?php

namespace Bernard;

use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer\NaiveSerializer;
use Bernard\ServiceResolver\ObjectResolver;

/**
 * Knows how to create consumers
 *
 * @package Bernard
 */
class ConsumerFactory
{

    /**
     * Create a new consumer
     *
     * @param Driver          $driver     Queue driver
     * @param array           $services   The services to be registered
     * @param Serializer      $serializer opt: Message serializer
     * @param ServiceResolver $resolver   opt: Service resolver
     *
     * @return Consumer
     */
    public function create(Driver $driver, array $services, Serializer $serializer = null, ServiceResolver $resolver = null)
    {
        if (!$serializer) {
            $serializer = new NaiveSerializer;
        }
        if (!$resolver) {
            $resolver = new ObjectResolver;
        }

        $queueFactory = new PersistentFactory($driver, $serializer);
        foreach ($services as $name => $service) {
            $resolver->register($name, $service);
        }

        return new Consumer($resolver);
    }
}