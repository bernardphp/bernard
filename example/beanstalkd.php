<?php

use Bernard\Driver\BeanstalkdDriver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    return new BeanstalkdDriver(new Pheanstalk_Pheanstalk('127.0.0.1'));
}

require 'bootstrap.php';
