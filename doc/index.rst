Bernard
=======

Welcome to Bernard's documentation!

Bernard is a task queue implemented in PHP. It makes it easier to create distributed
systems by having a structured message format and implementation. Bernard have it's
roots in Resque and supports Redis and many of the same features. For the very adventurous
people it should be possible to send Bernard messages to Requeue and vice versa.

The basic model that Bernard is build after is the "Producer-Consumer" design pattern where
it could be implemented in PHP.

Feedback is important and is **always** welcome. You can contact me via a `Pull Request on GitHub <http://github.com/henrikbjorn/Bernard>`_
or on twitter where i am `@henrikbjorn <http://twitter.com/henrikbjorn>`_.

Documentation
-------------

.. toctree::
    :maxdepth: 2

    getting-started
    drivers
    serializers
    consuming
    frameworks
    cookbook
    monitoring

----

.. image:: _static/peytzco.png
