Raekke
======

Raekke is a message queue implemented in php. It is very similiar to Resque and
allows for easy creation of workers and creating distributed systems.

[![Build Status](https://travis-ci.org/henrikbjorn/Raekke.png?branch=master)](https://travis-ci.org/henrikbjorn/Raekke)

Getting Started
---------------

Raekke allows you as Resque to create messages and place them on a queue. And
later on pull thoose off the queue and process them. It is not a a complete
solution to have any object method being called at a later time (as resque).

### Installing

The easiest way to install Raekke is by using [Composer](http://getcomposer.org).
If your projects do not already use this, it is highly recommended to start.

``` bash
$ composer require henrikbjorn/raekke:dev-master
```

### Configuring Predis

For storing messages Redis is used together with Predis as the communication
driver between php and Redis. This means Predis and Redis is required.

It is highly encourage to use a prefix for Predis (but not enforces) to make
sure your sets does not conflict with others of the same same.

``` php
<?php

use Raekke\Connection;
use Predis\Client;

$predis = new Client('tcp://localhost', array(
    'prefix' => 'raekke:',
));

$connection = new Connection($predis);
```

### Producing Messages

Any message sent to Raekke must be an instance of `Raekke\Message\MessageInterface`
which have a `getName` and `getQueue` method. `getName` is used when working on
messages and identifies the worker service that should work on it.

A message is given to a producer that send the message to the right queue.
It is also possible to get the queue directly from the queue factory and push
the message there. But remember to wrap the message in a `MessageWrapper` object.
The easiest is to give it to the producer as the queue name
is taken from the message object.

To make it easier to send messages and not require every type to be implemented
in a seperate class a `Raekke\Message\DefaultMessage` is provided. It can hold
any number of proberties and only needs a name for the message. The queue name
is then generated from that. When generating the queue name it will insert a "_"
before any uppercase letters and then lowercase everything.

Messages are serialized to json using [JMS Serializer](http://jmsyst.com/libs/serializer).
Therefor an instance of that is required. Also if custom message classes are
used it is needed to add metadata for being able to serialize and deserialize them.

``` php
<?php

use Raekke\Producer;
use Raekke\Message\DefaultMessage;
use Raekke\Message\MessageWrapper;
use Raekke\QueueFactory;
use Raekke\Serializer\Serializer;

// .. create serializer instance where src/Raekke/Resources/serializer
// is registered as a metadata dir with "Raekke" as prefix.
$serializer = new Serializer($jmsSerializer);

// .. create connection
$factory = new QueueFactory($connection, $serializer);
$producer = new Producer($factory);

$message = new DefaultMessage("SendNewsletter", array(
    'newsletterId' => 12,
));

$producer->publish($message);

// or give it to a queue directly
$factory->get('my-queue')->enqueue(new MessageWrapper($message));
```

### Consuming Messages

A single message represents a job that needs to be performed. And as described
earlier a message's name is used to determaine what service object should have
that message.

A service object can be any object that have a method corresponding to the message
name prefixed with on. So `new DefaultMessage('SendNewsletter')` will trigger a
call to `$serviceObject->onSendNewsletter`. For the system to know what service
object should handle what messages it is need to register them first.

``` php
<?php

use Raekke\ServiceResolver;
use Raekke\Consumer;

// .. create connection and a queuefactory
// NewsletterMessageHandler is a pseudo service object that responds to
// onSendNewsletter.

$serviceResolver = new ServiceResolver;
$serviceResolver->register('SendNewsletter', new NewsletterMessageHandler);

// Create a Consumer and start the loop. The second argument is optional and
// is the queue failed messages should be added to.
$consumer = new Consumer($serviceResolver, $queueFactory->create('failed'));
$consumer->consume($queueFactory->create('send-newsletter'));
```

Raekke comes with a `ConsumeCommand` which can be used with Symfony Console 
component.

``` php
<?php

use Raekke\Command\ConsumeCommand;

// create $console application
$console->add(new ConsumeCommand($services, $queueManager));
```

It can then be used as any other console command. The argument given should be
the queue that your messages is on. If we use the earlier example with sending
newsletter it would look like this.

``` bash
$ /path/to/console raekke:consume 'send-newsletter'
```

Integration with Frameworks
---------------------------

To make it easier to start up and have it "just work" with sending messages a
number of integrations have been created.

* Somebody should do this part...

Monitoring
----------

Having a message queue where it is not possible to what whats in queue and the
contents of the messages is not very handy. And for that there is [Juno](https://github.com/henrikbjorn/Juno).

It is implemented in Silex and is very lightweight. Also if needed it can be
embedded in other Silex or Flint applications.

![Juno](http://i.imgur.com/oZFzfKq.png)

Alternatives
------------

If this is not your cup of tea there exists other alternatives that might be
better for your needs.

* [php-resque](https://github.com/chrisboulton/php-resque)
* [Resque](https://github.com/defunkt/resque)


Happy Customers
---------------

Not really anybody as it is not completly finished. If you need something like
this i encourage you to contact me and we will figure out how we can work
together on this.
