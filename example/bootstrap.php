<?php

use Predis\Client;
use Bernard\Connection\PredisConnection;
use Bernard\Serializer\SymfonySerializer;
use Bernard\Symfony\EnvelopeNormalizer;
use Bernard\Broker\PersistentBroker;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_erros', 1);
error_reporting(E_ALL);

$serializer = new SymfonySerializer(new Serializer(array(new EnvelopeNormalizer), array(new JsonEncoder)));

$connection = new PredisConnection(new Client(null, array(
    'prefix' => 'bernard:',
)));
$broker = new PersistentBroker($connection, $serializer);
