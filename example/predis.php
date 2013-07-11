<?php

use Predis\Client;
use Bernard\Driver\PredisDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    return new PredisDriver(new Client(null, array(
        'prefix' => 'bernard:',
    )));
}

require 'bootstrap.php';
