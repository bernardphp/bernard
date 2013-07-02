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

        $schema = new MessagesSchema;
        $connection->getSchemaManager()->createTable($schema->createTable());

    .. code-block:: sql

        CREATE TABLE `bernard_messages` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `queue` varchar(255) DEFAULT NULL,
            `message` longtext,
            PRIMARY KEY (`id`),
            KEY `queue_idx` (`queue`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8


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

IronMQ from Iron.io is a "message queue in the cloud".

.. important::

    You need to create an account with iron.io to get a ``project-id`` and ``token``.

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

Amazon SQS
----------

SQS (Simple Queuing System) part of Amazons Web Services (AWS).

.. important::

    You need to create an account with AWS to get SQS access credentials, consisting of an API key
    and an API secret. In addition, each SQS queue is setup in a specific region, eg ``eu-west-1``
    or ``us-east-1``.

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
