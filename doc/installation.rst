Installing
==========

Bernard is a framework for integration a message queue into your application.
It does this by providing a number of different connections for different backends like
Redis, Predis and others.

As it is a framework it not a plug-and-play library like php-resque.

Installation
------------

The only real way of installing is thruogh Composer. Create a ``composer.json`` file and run
``composer.phar henrikbjorn/bernard update``:

.. code-block:: json

    {
        "require" : {
            "henrikbjorn/bernard" : "~0.2"
        }
    }
