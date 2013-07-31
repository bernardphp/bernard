<?php

namespace Bernard;

use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer\NaiveSerializer;

/**
 * Knows how to create consumers
 *
 * @package Bernard
 */
class ProducerFactory
{

    /**
     * Create a new producer
     *
     * @param Driver     $driver     Queue driver
     * @param Serializer $serializer opt: Message serializer
     *
     * @return Producer
     */
    public function create(Driver $driver, Serializer $serializer = null)
    {
        if (!$serializer) {
            $serializer = new NaiveSerializer;
        }
        $queueFactory = new PersistentFactory($driver, $serializer);

        return new Producer($queueFactory);
    }
}