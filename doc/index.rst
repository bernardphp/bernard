Getting Started
===============

Installation
------------

The recommended way to install Bernard is using `Composer <http://getcomposer.org>`_.
If your projects do not already use this, it is highly recommended to start
using it.

To install Bernard, run:

.. code-block:: bash

    $ composer require bernard/bernard

Then look at what kind of drivers and serializers are available and install
the ones you need before you are going to use Bernard.

Examples
--------

There are numerous examples of running Bernard in the ``example`` directory. The files are
named after the driver they are using. Each file takes the argument ``consume`` or ``produce``.
For instance, to use the ``Predis`` driver, use:

.. code-block:: sh

    $ php ./example/predis.php consume
    $ php ./example/predis.php produce

And you would see properly a lot of output showing an error. This is because
the ``ErrorLogMiddleware`` is registered and shows all exceptions. In this case,
the exception is caused by ``rand()`` always returning 7.

This directory is a good source for setting stuff up and can be used as a go to guide.

.. toctree::
    :maxdepth: 2
    :hidden:

    self
    producing
    queues
    drivers
    serializers
    consuming
    frameworks
    cookbook
