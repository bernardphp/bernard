Drivers
=======

Several different types of drivers are supported. Currently theese are available:

Redis Extension
---------------

Requires the installation of the pecl extension. You can add the following to your ``composer.json`` file
to make sure it is installed:

.. configuration-block::

    .. code-block:: json

        {
            "require" : {
                "ext-redis" : "~2.2"
            }
        }

    .. code-block:: php

        <?php

        use Bernard\Driver\PhpRedisDriver;

        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $redis->setOption(Redis::OPT_PREFIX, 'bernard:');

        $driver = new PhpRedisDriver($redis);

Predis
------

Requires the installation of predis. Add the following to your ``composer.json`` file for this:

.. configuration-block::

    .. code-block:: json

        {
            "require" : {
                "predis/predis" : "~0.8"
            }
        }

    .. code-block:: php

        <?php

        use Bernard\Driver\PredisDriver;
        use Predis\Client;

        $predis = new Client('tcp://localhost', array(
            'prefix' => 'bernard:',
        ));

        $driver = new PredisDriver($predis);

Doctrine DBAL
-------------

For small usecases or testing there is a Doctrine DBAL driver which supports all of the major
database platforms.

The driver uses transactions to make sure that a single consumer always get the message popped from the queue.

.. important::

    To use Doctrine DBAL remember to setup the correct schema.

Use one of the following methods for creating your table.

.. configuration-block::

    .. code-block:: php

        <?php

        use Bernard\Doctrine\MessagesSchema;
        use Doctrine\DBAL\Schema\Schema;

        MessagesSchema::create($schema = new Schema);
        
        // setup $connection with Doctrine DBAL
        $sql = $schema->toSql($connection->getDatabasePlatform());


.. configuration-block::

    .. code-block:: json

        {
            "require" : {
                "doctrine/dbal" : "~2.3"
            }
        }

    .. code-block:: php

        <?php

        use Bernard\Driver\DoctrineDriver;
        use Doctrine\DBAL\DriverManager;

        $connection = DriverManager::getConnection(array(
            'dbname' => 'bernard',
            'user' => 'root',
            'password' => null,
            'driver' => 'pdo_mysql',
        ));


        $driver = new DoctrineDriver($connection);

IronMQ
------

IronMQ from Iron.io is a "message queue in the cloud". The IronMQ driver supports prefetching
messages, which reduces the number of http request. This is configured as the second parameter
in the drivers constructor.

.. important::

    You need to create an account with iron.io to get a ``project-id`` and ``token``.

.. important::

    When using prefetching the timeout value for each message much be greater than the time it takes to
    consume all of the fetched message. If one message takes 10 seconds to consume and the driver is prefetching
    5 message the timeout value must be greater than 10 seconds.

.. configuration-block::

    .. code-block:: json

        {
            "require" : {
                "iron-io/iron_mq" : "~1.4"
            }
        }

    .. code-block:: php

        <?php

        use Bernard\Driver\IronMqDriver;

        $connection = new IronMQ(array(
            'token'      => 'your-ironmq-token',
            'project_id' => 'your-ironmq-project-id',
        ));


        $driver = new IronMqDriver($connection);

        // or with a prefetching number
        $driver = new IronMqDriver($connection, 5);

It is also possible to use push queues with some additional logic. Basically
it is needed to deserialize the message in the request and route it to the
correct service. An example of this:

.. code-block:: php

    <?php

    namespace Acme\Controller;

    use Bernard\Serializer;
    use Bernard\ServiceResolver;
    use Bernard\ServiceResolver\Invoker;
    use Symfony\Component\HttpFoundation\Request;

    class QueueController
    {
        public function __construct(ServiceResolver $resolver, Serializer $serializer)
        {
            $this->resolver = $resolver;
            $this->serializer = $serializer;
        }

        public function queueAction(Request $request)
        {
            $envelope = $this->serializer->deserialize($request->getContent());

            $invoker = new Invoker($this->resolver->resolve($envelope);
            $invoker->invoke($envelope));
        }
    }

Amazon SQS
----------

SQS (Simple Queuing System) part of Amazons Web Services (AWS). The SQS driver supports prefetching messages
which reduces the number of http request. It also supports aliasing specific queue urls to a queue name. If queue
aliasing is used the queue names provided will not require a HTTP request to amazon to be resolved.

.. important::

    You need to create an account with AWS to get SQS access credentials, consisting of an API key
    and an API secret. In addition, each SQS queue is setup in a specific region, eg ``eu-west-1``
    or ``us-east-1``.

.. important::

    When using prefetching the timeout value for each message much be greater than the time it takes to
    consume all of the fetched message. If one message takes 10 seconds to consume and the driver is prefetching
    5 message the timeout value must be greater than 10 seconds.

.. configuration-block::

    .. code-block:: json

        {
            "require" : {
                "aws/aws-sdk-php" : "~2.4"
            }
        }

    .. code-block:: php

        <?php

        use Aws\Sqs\SqsClient;
        use Bernard\Driver\SqsDriver;

        $connection = SqsClient::factory(array(
            'key'    => 'your-aws-access-key',
            'secret' => 'your-aws-secret-key',
            'region' => 'the-aws-region-you-choose'
        ));

        $driver = new SqsDriver($connection);

        // or with prefetching
        $driver = new SqsDriver($connection, array(), 5);

        // or with aliased queue urls
        $driver = new SqsDriver($connection, array(
            'queue-name' => 'queue-url',
        ));
