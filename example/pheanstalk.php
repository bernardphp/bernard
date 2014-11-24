<?php

use Pheanstalk\Pheanstalk;
use Bernard\Driver\PheanstalkDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $pheanstalk = new Pheanstalk('localhost');

    return new PheanstalkDriver($pheanstalk);
}

require 'bootstrap.php';
