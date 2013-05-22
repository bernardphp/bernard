<?php

use Predis\Client;
use Bernard\Driver\PredisDriver;
use Bernard\Serializer\SymfonySerializer;
use Bernard\Symfony\EnvelopeNormalizer;
use Bernard\Symfony\DefaultMessageNormalizer;
use Bernard\QueueFactory\PersistentFactory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_erros', 1);
error_reporting(E_ALL);

$normalizers = array(new EnvelopeNormalizer, new DefaultMessageNormalizer);
$serializer = new SymfonySerializer(
    new Serializer($normalizers, array(new JsonEncoder))
);

$connection = new PredisDriver(new Client(null, array(
    'prefix' => 'bernard:',
)));
$queues = new PersistentFactory($connection, $serializer);
