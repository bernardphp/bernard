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
    use Bernard\ServiceResolver\ForkingResolver;
    use Spork\ProcessManager;

    // create a normal resolver and register some services.
    $serviceResolver = new ObjectResolver;

    // create a process manager
    $spork = new ProcessManager;

    $serviceResolver = new ForkingResolver($spork, $serviceResolver);

.. warning::

    Because the forked process cannot throw an exception to its parent all exceptions that happens while invoking
    the service object will be wrapped with a ``Bernard\Exception\ForkingLogicException``.
