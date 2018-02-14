<?php

use Predis\Client;
use Bernard\Driver\Predis\Driver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver()
{
    return new Driver(new Client(null, [
        'prefix' => 'bernard:',
    ]));
}

require 'bootstrap.php';
