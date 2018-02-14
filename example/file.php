<?php

use Bernard\Driver\FlatFile\Driver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */

function get_driver() {
    $baseDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bernard';

    if (!is_dir($baseDir)) {
        mkdir($baseDir);
    }

    return new Driver($baseDir);
}

require 'bootstrap.php';
