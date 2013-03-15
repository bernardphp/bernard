<?php

use JMS\Serializer\SerializerBuilder;
use Predis\Client;
use Bernard\Connection\PredisConnection;
use Bernard\Serializer\JMSSerializer;
use Bernard\QueueFactory\PersistentFactory;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_erros', 1);
error_reporting(E_ALL);

$jmsSerializer = SerializerBuilder::create()
    ->addMetadataDir(__DIR__ . '/../src/Bernard/Resources/serializer', 'Bernard')
    ->build();

$connection = new PredisConnection(new Client(null, array(
    'prefix' => 'raekke:',
)));
$serializer = new JMSSerializer($jmsSerializer);
$queues = new PersistentFactory($connection, $serializer);
