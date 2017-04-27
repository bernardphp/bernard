<?php

use Bernard\Driver\MemcachedDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $memcached = new \Memcached();
    $memcached->addServer('localhost', 11211);

    return new MemcachedDriver($memcached);
}

require 'bootstrap.php';
