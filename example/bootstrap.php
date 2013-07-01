<?php

use Predis\Client;
use Bernard\Driver\IronMqDriver;
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

// with Redis
$connection = new PredisDriver(new Client(null, array(
    'prefix' => 'bernard:',
)));

// with IronMq
$ironmq = new IronMQ(array(
    'token'      => getenv('IRONMQ_TOKEN'),
    'project_id' => getenv('IRONMQ_PROJECT_ID'),
));
$connection = new Bernard\Driver\IronMqDriver($ironmq);

print_r($ironmq->getQueue('echo-time'));

// with Sqs
/*$sqs = Aws\Sqs\SqsClient::factory(array(
    'key'    => getenv('ACCESS_KEY'),
    'secret' => getenv('SECRET_KEY'),
    'region' => getenv('EC2_REGION')
));
$connection = new Bernard\Driver\SqsDriver($sqs);*/

$queues = new PersistentFactory($connection, $serializer);
