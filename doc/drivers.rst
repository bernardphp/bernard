Drivers
=======

Several different types of drivers are supported. Currently these are available:

* `Google AppEngine`_
* `Doctrine DBAL`_
* `Flatfile`_
* `IronMQ`_
* `MongoDB`_
* `Pheanstalk`_
* `PhpAmqp / RabbitMQ`_
* `Redis Extension`_
* `Predis`_
* `Amazon SQS`_
* `Queue Interop`_

Google AppEngine
----------------

The Google AppEngine has support for PHP and PushQueue just as IronMQ. The AppEngine driver for Bernard is a minimal driver
that uses its TaskQueue to push messages.
Visit the `official docs <https://developers.google.com/appengine/docs/php/taskqueue/overview-push>`_ to get more information on the
usage of the AppEngine api.

.. important::

    This driver only works on AppEngine or with its development server as it
    needs access to its SDK. It must also be autoloadable. If it is in the
    include path you can use ``"config" : { "use-include-path" : true } }`` in
    Composer.

The driver takes a list of queue names and mappings to an endpoint. This is
because queues are created at runtime and their endpoints are not
preconfigured.

.. code-block:: php

    <?php

    use Bernard\Driver\AppEngine\Driver;

    $driver = new Driver(array(
        'queue-name' => '/url_endpoint',
    ));

To consume messages, you need to create an url endpoint matching the one given
to the drivers constructor. For the actual dispatching of messages, you can do
something like this:

.. code-block:: php

    <?php

    namespace Acme\Controller;

    use Bernard\Consumer
    use Bernard\Serializer;
    use Bernard\QueueFactory;
    use Symfony\Component\HttpFoundation\Request;

    class QueueController
    {
        protected $consumer;
        protected $queues;
        protected $serializer;

        public function __construct(Consumer $consumer, QueueFactory $queues, Serializer $serializer)
        {
            $this->consumer = $consumer;
            $this->queues = $queues;
            $this->serializer = $serializer;
        }

        public function queueAction(Request $request)
        {
            $envelope = $this->serializer->deserialize($request->getContent());

            // This will invoke the right service and middleware, and lastly it will acknowledge
            // the message.
            $this->consumer->invoke($envelope, $this->queues->create($envelope->getMessage()->getQueue()));
        }
    }

Doctrine DBAL
-------------

For small usecases or testing, there is a Doctrine DBAL driver which supports
all of the major database platforms.

The driver uses transactions to make sure that a single consumer always get
the message popped from the queue.

.. important::

    To use Doctrine DBAL remember to setup the correct schema.

Creating the needed bernard tables can be automated by creating a console
application with `custom commands <http://doctrine-orm.readthedocs.org/en/stable/reference/tools.html#adding-own-commands>`_.
Just configure a `connection <http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection>`_
or `entity manager <http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/tutorials/getting-started.html#obtaining-the-entitymanager>`_
as appropriate for your use case.

.. code-block:: php

    <?php
    // doctrine.php

    use Bernard\Driver\Docrtrine\Command as BernardCommands;
    use Doctrine\DBAL\Tools\Console\ConsoleRunner;
    use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Helper\HelperSet;

    $connection = ...;
    $commands = [
        new BernardCommands\CreateCommand(),
        new BernardCommands\DropCommand(),
        new BernardCommands\UpdateCommand(),
    ];

    // To create a new application from scratch ...
    $helperSet = new HelperSet(['connection' => new ConnectionHelper($connection)]);
    $cli = new Application('Bernard Doctrine Command Line Interface');
    $cli->setCatchExceptions(true);
    $cli->setHelperSet($helperSet);
    $cli->addCommands($commands);

    // ... or, if you're using Doctrine ORM 2.5+,
    // just re-use the existing Doctrine application ...
    $entityManager = ...;
    $helperSet = ConsoleRunner::createHelperSet($entityManager);
    $cli = ConsoleRunner::createApplication($helperSet, $commands);

    // Finally, run the application
    $cli->run();

And run the console application like so:

.. code-block:: shell

    php doctrine.php bernard:doctrine:create

Alternatively, use the following method for creating the tables manually.

.. code-block:: php

    <?php

    use Bernard\Driver\Doctrine\MessagesSchema;
    use Doctrine\DBAL\Schema\Schema;

    MessagesSchema::create($schema = new Schema);

    // setup Doctrine DBAL
    $connection = ...;

    $sql = $schema->toSql($connection->getDatabasePlatform());

    foreach ($sql as $query) {
        $connection->exec($query);
    }

And here is the setup of the driver for doctrine dbal:

.. code-block:: json

    {
        "require" : {
            "doctrine/dbal" : "~2.3"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\Doctrine\Driver;
    use Doctrine\DBAL\DriverManager;

    $connection = DriverManager::getConnection(array(
        'dbname'   => 'bernard',
        'user'     => 'root',
        'password' => null,
        'driver'   => 'pdo_mysql',
    ));


    $driver = new Driver($connection);

Flatfile
--------

The flat file driver provides a simple job queue without any database

.. code-block:: php

    <?php

    use Bernard\Driver\FlatFile\Driver;

    $driver = new Driver('/dir/to/store/messages');

IronMQ
------

IronMQ from Iron.io is a "message queue in the cloud". The IronMQ driver supports prefetching
messages, which reduces the number of HTTP request. This is configured as the second parameter
in the drivers constructor.

.. important::

    You need to create an account with iron.io to get a ``project-id`` and ``token``.

.. important::

    When using prefetching the timeout value for each message much be greater than the time it takes to
    consume all of the fetched message. If one message takes 10 seconds to consume and the driver is prefetching
    5 message the timeout value must be greater than 10 seconds.

.. code-block:: json

    {
        "require" : {
            "iron-io/iron_mq" : "~1.4"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\IronMQ\Driver;

    $connection = new IronMQ(array(
        'token'      => 'your-ironmq-token',
        'project_id' => 'your-ironmq-project-id',
    ));


    $driver = new Driver($connection);

    // or with a prefetching number
    $driver = new Driver($connection, 5);

It is also possible to use push queues with some additional logic. Basically,
it is needed to deserialize the message in the request and route it to the
correct service. An example of this:

.. code-block:: php

    <?php

    namespace Acme\Controller;

    use Bernard\Consumer
    use Bernard\Serializer;
    use Bernard\QueueFactory;
    use Symfony\Component\HttpFoundation\Request;

    class QueueController
    {
        protected $consumer;
        protected $queues;
        protected $serializer;

        public function __construct(Consumer $consumer, QueueFactory $queues, Serializer $serializer)
        {
            $this->consumer = $consumer;
            $this->queues = $queues;
            $this->serializer = $serializer;
        }

        public function queueAction(Request $request)
        {
            $envelope = $this->serializer->deserialize($request->getContent());

            // This will invoke the right service and middleware, and lastly it will acknowledge
            // the message.
            $this->consumer->invoke($envelope, $this->queues->create($envelope->getMessage()->getQueue()));
        }
    }

MongoDB
-------

The MongoDB driver requires the `mongo PECL extension <http://pecl.php.net/package/mongo>`_.
On platforms where the PECL extension is unavailable, such as HHVM,
`mongofill <https://github.com/mongofill/mongofill>`_ may be used instead.

The driver should be constructed with two MongoCollection objects, which
corresponding to the queue and message collections, respectively.

.. code-block:: php

    <?php

    $mongoClient = new \MongoClient();
    $driver = new \Bernard\Driver\MongoDB\Driver(
        $mongoClient->selectCollection('bernardDatabase', 'queues'),
        $mongoClient->selectCollection('bernardDatabase', 'messages'),
    );

.. note::

    If you are using Doctrine MongoDB or the ODM, you can access the
    MongoCollection objects through the ``getMongoCollection()`` method on the
    ``Doctrine\MongoDB\Collection`` wrapper class, which in turn may be
    retrieved from a ``Doctrine\MongoDB\Database`` wrapper or DocumentManager
    directly.

To support message queries, the following index should also be created:

.. code-block:: php

    <?php

    $mongoClient = new \MongoClient();
    $collection = $mongoClient->selectCollection('bernardDatabase', 'messages');
    $collection->createIndex([
        'queue' => 1,
        'visible' => 1,
        'sentAt' => 1,
    ]);

Pheanstalk
----------

Requires the installation of pda/pheanstalk. Add the following to your
``composer.json`` file for this:

.. code-block:: json

    {
        "require" : {
            "pda/pheanstalk" : "~3.0"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\Pheanstalk\Driver;
    use Pheanstalk\Pheanstalk;

    $pheanstalk = new Pheanstalk('localhost');

    $driver = new Driver($pheanstalk);

PhpAmqp / RabbitMQ
------------------

The RabbitMQ driver uses the `php-amqp library by php-amqplib <https://github.com/php-amqplib/php-amqplib>`_.

The driver should be constructed with a class that extends `AbstractConnection` (for example `AMQPStreamConnection` or `AMQPSocketConnection`),
an exchange name and optionally the default message parameters.

.. code-block:: php

    <?php

    $connection = new \PhpAmqpLib\Connection\AMQPStreamConnection('localhost', 5672, 'foo', 'bar');

    $driver = new \Bernard\Driver\PhpAmqpDriver($connection, 'my-exchange');

    // Or with default message params
    $driver = new \Bernard\Driver\PhpAmqpDriver(
        $connection,
        'my-exchange',
        ['content_type' => 'application/json', 'delivery_mode' => 2]
    );

Redis Extension
---------------

Requires the installation of the pecl extension. You can add the following to
your ``composer.json`` file, to make sure it is installed:

.. code-block:: json

    {
        "require" : {
            "ext-redis" : "~2.2"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\PhpRedis\Driver;

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->setOption(Redis::OPT_PREFIX, 'bernard:');

    $driver = new Driver($redis);

Predis
------

Requires the installation of predis. Add the following to your
``composer.json`` file for this:

.. code-block:: json

    {
        "require" : {
            "predis/predis" : "~0.8"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\Predis\Driver;
    use Predis\Client;

    $predis = new Client('tcp://localhost', array(
        'prefix' => 'bernard:',
    ));

    $driver = new Driver($predis);

Amazon SQS
----------

This driver implements the SQS (Simple Queuing System) part of Amazons Web
Services (AWS). The SQS driver supports prefetching messages which reduces the
number of HTTP request.  It also supports aliasing specific queue urls to a
queue name. If queue aliasing is used the queue names provided will not
require a HTTP request to amazon to be resolved.

.. important::

    You need to create an account with AWS to get SQS access credentials,
    consisting of an API key and an API secret. In addition, each SQS queue is
    setup in a specific region, eg ``eu-west-1`` or ``us-east-1``.

.. important::

    When using prefetching, the timeout value for each message should be greater
    than the time it takes to consume all of the fetched message. If one
    message takes 10 seconds to consume and the driver is prefetching 5
    message the timeout value must be greater than 10 seconds.

.. code-block:: json

    {
        "require" : {
            "aws/aws-sdk-php" : "~2.4"
        }
    }

.. code-block:: php

    <?php

    use Aws\Sqs\SqsClient;
    use Bernard\Driver\Sqs\Driver;

    $connection = SqsClient::factory(array(
        'key'    => 'your-aws-access-key',
        'secret' => 'your-aws-secret-key',
        'region' => 'the-aws-region-you-choose'
    ));

    $driver = new Driver($connection);

    // or with prefetching
    $driver = new Driver($connection, array(), 5);

    // or with aliased queue urls
    $driver = new Driver($connection, array(
        'queue-name' => 'queue-url',
    ));

Queue Interop
-------------

This driver adds ability to use any `queue interop <https://github.com/queue-interop/queue-interop#implementations>`_ compatible transport.
For example we choose enqueue/fs one to demonstrate how it is working.

.. code-block:: json

    {
        "require" : {
            "enqueue/fs" : "^0.7"
        }
    }

.. code-block:: php

    <?php

    use Bernard\Driver\Interop\Driver;
    use Enqueue\Fs\FsConnectionFactory;

    $context = (new FsConnectionFactory('file://'.__DIR__.'/queues'))->createContext();

    $driver = new InteropDriver($context);
