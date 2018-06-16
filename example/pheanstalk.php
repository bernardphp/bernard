<?php

use Pheanstalk\Pheanstalk;
use Bernard\Driver\Pheanstalk\Driver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver()
{
    $pheanstalk = new Pheanstalk('localhost');

    return new Driver($pheanstalk);
}

require 'bootstrap.php';
