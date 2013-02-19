Raekke
======

"Raekke" is a message queue implemented in PHP using a Redis backend. It is very similiar to Resque but uses a more 
service objected approach instead of static worker classes.

Configuring
-----------

Raekke allows you as Resque to create messages and place them on a queue. And later on
pull thoose off the queue and process them. It is not a a complete solution to have
any object being called later.

For storing messages Redis is used together with Predis as the communication driver between
php and Redis. This means Predis and Redis is required.

It is highly encourage to use a prefix for Predis (but not enforces) to make sure your sets does not conflict
with others of the same same.

``` php
<?php

use Raekke\Connection;
use Predis\Client;

$predis = new Client('tcp://localhost', array(
    'prefix' => 'raekke:',
));

$connection = new Connection($predis);
```

Sending Messages
----------------

Any message sent to Raekke must be an instance of `Raekke\Message\MessageInterface` which have a 
`getName` and `getQueue` method. `getName` is used when working on messages and identifies
the worker service that should work on it. More on this later on.

A message is given to a manager that handles queues or directly to a queue object. The easiest
is to give it to the manager as the queue name is taken from the message object.

To make it easier to send messages and not require every type to be implemented in a seperate
class a `Raekke\Message\DefaultMessage` is provided. It can hold any number of proberties and only
needs a name for the message. The queue name is then generated from that. When generating the queue
name it will insert a "_" before any uppercase letters and then lowercase everything.

Because every message is serialized with `serialize` it must support being that and should only
contain simple values.

``` php
<?php

use Raekke\Message\DefaultMessage;
use Raekke\QueueManager;

// .. create connection
$manager = new QueueManager($connection);

$message = new DefaultMessage("SendNewsletter", array(
    'newsletterId' => 12,
));

$manager->enqueue($message);

// or get the queue and specify the queue name freely
$queue = $manager->get('custom-queue');
$queue->push($message);
```
