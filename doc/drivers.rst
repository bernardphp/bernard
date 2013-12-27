Drivers
=======

Several different types of drivers are supported. Currently these are available:

* `Redis Extension`_
* `Predis`_
* `Doctrine DBAL`_
* `IronMQ`_
* `Amazon SQS`_
* `Google AppEngine`_

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

    use Bernard\Driver\PhpRedisDriver;

    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->setOption(Redis::OPT_PREFIX, 'bernard:');

    $driver = new PhpRedisDriver($redis);

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

    use Bernard\Driver\PredisDriver;
    use Predis\Client;

    $predis = new Client('tcp://localhost', array(
        'prefix' => 'bernard:',
    ));

    $driver = new PredisDriver($predis);

Doctrine DBAL
-------------

For small usecases or testing, there is a Doctrine DBAL driver which supports
all of the major database platforms.

The driver uses transactions to make sure that a single consumer always get
the message popped from the queue.

.. important::

    To use Doctrine DBAL remember to setup the correct schema.

Use the following method for creating the needed bernard tables.

.. code-block:: php

    <?php

    use Bernard\Doctrine\MessagesSchema;
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

    use Bernard\Driver\DoctrineDriver;
    use Doctrine\DBAL\DriverManager;

    $connection = DriverManager::getConnection(array(
        'dbname'   => 'bernard',
        'user'     => 'root',
        'password' => null,
        'driver'   => 'pdo_mysql',
    ));


    $driver = new DoctrineDriver($connection);

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

    use Bernard\Driver\IronMqDriver;

    $connection = new IronMQ(array(
        'token'      => 'your-ironmq-token',
        'project_id' => 'your-ironmq-project-id',
    ));


    $driver = new IronMqDriver($connection);

    // or with a prefetching number
    $driver = new IronMqDriver($connection, 5);

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

Google AppEngine
----------------

The Google AppEngine has support for PHP and PushQueue just as IronMQ. The
AppEngine driver for Bernard is a minimal driver that uses its TaskQueue to
push messages. There is a lot about how this works in
`their documentation <https://developers.google.com/appengine/docs/php/taskqueue/overview-push>`_.

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

    use Bernard\Driver\AppEngineDriver;

    $driver = new AppEngineDriver(array(
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

