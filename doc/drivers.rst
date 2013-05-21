Drivers
=======

Several different types of drivers are supported. Currently theese are available:

Redis Extension
---------------

Requires the installation of the pecl extension. You can add the following to your ``composer.json`` file
to make sure it is installed:

.. code-block:: json

    {
        "require" : {
            "ext-redis" : "~2.2"
        }
    }

And then instanciate the correct driver object.

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

.. code-block:: json

    {
        "require" : {
            "predis/predis" : "~0.8"
        }
    }

And then instanciate the correct driver object.

.. code-block:: php

    <?php

    use Bernard\Driver\PredisDriver;
    use Predis\Client;

    $predis = new Client('tcp://localhost', array(
        'prefix' => 'bernard:',
    ));

    $driver = new PredisDriver($predis);
