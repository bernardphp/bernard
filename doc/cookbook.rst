Cookbook
========

Monitoring
----------

Having a message queue where it is not possible to know what is in the queue and the
contents of the messages is not very handy, so for that there is `Juno <https://github.com/henrikbjorn/Juno>`_.

It is implemented in Silex and is very lightweight. Also if needed, it can be
embedded in other Silex or Flint applications.

Fork jobs with Spork
--------------------

Spork is a Forking library created by Kris Wallsmith and can be found at `Spork <https://github.com/kriswallsmith/spork>`_.
Bernard provides integration to Spork for forking before invoking the correct service.

It works by decorating the normal service resolver.

.. code-block:: php

    <?php

    use Bernard\ServiceResolver\ObjectResolver;
    use Bernard\Spork\ProcessDecoratingResolver;
    use Spork\ProcessManager;

    // create a normal resolver and register some services.
    $serviceResolver = new ObjectResolver;

    // create a process manager
    $spork = new ProcessManager;

    $serviceResolver = new ProcessDecoratingResolver($spork, $serviceResolver);

.. warning::

    Because the forked process cannot throw an exception to its parent all exceptions that happens while invoking
    the service object will be wrapped with a ``Bernard\Spork\Exception\ProcessException``.

.. warning::

    When PHP fork's out a process and if you have a PDO connection it will have to be reconnected. This is also applicaple
    with Doctrine DBAL and can be done like:

    .. code-block:: php

        <?php

        // Doctrine DBAL Connection will automatically recreate a PDO instance which is the same as a reconnect.
        $connection->close();

    This must be done when inside the Fork.


