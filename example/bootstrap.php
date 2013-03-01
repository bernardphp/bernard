<?php

use JMS\Serializer\SerializerBuilder;
use Predis\Client;
use Raekke\Connection;
use Raekke\Serializer\Serializer;
use Raekke\QueueFactory\QueueFactoryInterface;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_erros', 1);
error_reporting(E_ALL);

$jmsSerializer = JMS\Serializer\SerializerBuilder::create()
    ->addMetadataDir(__DIR__ . '/../src/Raekke/Resources/serializer', 'Raekke')
    ->build();

$connection = new Connection(new Client(null, array(
    'prefix' => 'raekke:',
)));
$serializer = new Serializer($jmsSerializer);
$queues = new QueueFactory($connection, $serializer);
