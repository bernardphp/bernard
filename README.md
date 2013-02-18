Raekke
======

"Raekke" is a message queue implemented in PHP using a Redis backend. It is very similiar to Resque but uses a more 
service objected approach instead of static worker classes.

Sending Messages
----------------

It is easy to get started using Raekke and sending messages to it.

``` php
<?php

use Raekke\Driver\Connection;
use Raekke\Driver\Configuration;
use Raekke\QueueManager;

$queueManager = new QueueManager(new Connection('tcp://localhost', new Configuration()));

$message = new DefaultMessage("Import", array(
    'file' => '/path/to/file.xml',
));

$queueManager->enqueue($message);
```

The queue to push the message to was not created before. This is because sets in redis are created on demand.

The first constructor parameter is the name of the message. This is later used to find the right worker service to pass 
it to. The `DefaultMessage` class determaines the queue used from its name. It is possible to enqueue a message to a specific
queue by getting a queue object from the queue manager.

``` php
<?php

$queue = $queueManager->get('my-queue');
$queue->push($message);
```

`DefaultMessage` makes it easy to start and send messages but it is encouraged to implement specific message classes
that extend from `Raekke\Message\Message`.

Working on Messages
-------------------

Not written yet.
