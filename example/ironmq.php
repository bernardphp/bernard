<?php

use IronMQ\IronMQ;
use Bernard\Driver\IronMqDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $ironmq = new IronMQ(array(
        'token'      => getenv('IRONMQ_TOKEN'),
        'project_id' => getenv('IRONMQ_PROJECT_ID'),
    ));

    return new IronMqDriver($ironmq);
}

if (!getenv('IRONMQ_TOKEN') || !getenv('IRONMQ_PROJECT_ID')) {
    die('Missing ENV variables. Make sure IRONMQ_TOKEN and IRONMQ_PROJECT_ID are set');
}

require 'bootstrap.php';
