<?php

use Bernard\Driver\PhpRedisDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $redis = new Redis;
    $redis->connect('localhost');
    $redis->setOption(Redis::OPT_PREFIX, 'bernard:');

    return new PhpRedisDriver($redis);
}

require 'bootstrap.php';
