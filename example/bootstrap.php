<?php

use JMS\Serializer\SerializerBuilder;
use Predis\Client;
use Raekke\Connection\PredisConnection;
use Raekke\Serializer\JMSSerializer;
use Raekke\QueueFactory\PersistentFactory;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_erros', 1);
error_reporting(E_ALL);

$jmsSerializer = SerializerBuilder::create()
    ->addMetadataDir(__DIR__ . '/../src/Raekke/Resources/serializer', 'Raekke')
    ->build();

$connection = new PredisConnection(new Client(null, array(
    'prefix' => 'raekke:',
)));
$serializer = new JMSSerializer($jmsSerializer);
$queues = new PersistentFactory($connection, $serializer);
