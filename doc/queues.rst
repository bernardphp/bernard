Queues
======

Bernard comes with a few built-in queues

Persistent queue
----------------

The default queue to use, it produces message to and consumes messages from a driver's queue

Roundrobin queue
----------------

With the roundrobin queue you can produce messages to multiple queues

In Memory Queue
---------------

Bernard comes with an implementation for ``SplQueue`` which is completly in memory.
It is useful for development and/or testing, when you don't necessarily want actions to be
performed.

