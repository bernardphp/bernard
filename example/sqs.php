<?php

use Aws\Sqs\SqsClient;
use Bernard\Driver\Sqs\Driver;

/**
 * Must be defined before including bootstrap.php
 * as this is the only custom part in the example.
 */
function get_driver() {
    $sqs = SqsClient::factory(array(
        'key'    => getenv('ACCESS_KEY'),
        'secret' => getenv('SECRET_KEY'),
        'region' => getenv('SQS_REGION')
    ));

    return new Driver($sqs);
}

if (!getenv('ACCESS_KEY') || !getenv('SECRET_KEY') || !getenv('SQS_REGION')) {
    die('Missing ENV variables. Make sure ACCESS_KEY, SECRET_KEY and SQS_REGION are set');
}

require 'bootstrap.php';
