Bernard
======

Bernard is a message queue implemented in php. It is very similiar to Resque and
allows for easy creation of workers and creating distributed systems.

[![Build Status](https://travis-ci.org/henrikbjorn/Bernard.png?branch=master)](https://travis-ci.org/henrikbjorn/Bernard)

Getting Started
---------------

Bernard allows you as Resque to create messages and place them on a queue, and
later on pull those off the queue and process them. It is not a complete
solution to have any object method being called at a later time (as resque).

### Installing

The easiest way to install Bernard is by using [Composer](http://getcomposer.org).
If your projects do not already use this, it is highly recommended to start.

``` bash
$ composer require henrikbjorn/bernard:dev-master
```

### Examples

In the `example` directory there are two examples of running Bernard. `producer.php` will
connect to redis on localhost and produce `EchoTime` messages. `consumer.php` will consume
theese and print the timestamp.

`in_memory.php` will produce 20 `EchoTime` messages and consume them right they
have been sent. It uses `SplQueue` and does not need a redis backend.

### Configuring Predis / PhpRedis

For storing messages Redis is used together with Predis as the communication
driver between php and Redis. This means Predis and Redis is required.

It is highly encouraged to use a prefix for Predis (but not enforced) to make
sure your sets do not conflict with others of the same name.

``` php
<?php

use Bernard\Connection\PredisConnection;
use Predis\Client;

$predis = new Client('tcp://localhost', array(
    'prefix' => 'bernard:',
));

$connection = new PredisConnection($predis);
```

If you use [PhpRedis](https://github.com/nicolasff/phpredis) instead of Predis:

``` php
<?php

use Bernard\Connection\PhpRedisConnection;

$connection = new PhpRedisConnection(new Redis());
```

### Producing Messages

Any message sent to Bernard must be an instance of `Bernard\Message`
which have a `getName` and `getQueue` method. `getName` is used when working on
messages and identifies the worker service that should work on it.

A message is given to a producer that sends the message to the right queue.
It is also possible to get the queue directly from the queue factory and push
the message there. But remember to wrap the message in an `Envelope` object.
The easiest way is to give it to the producer as the queue name
is taken from the message object.

To make it easier to send messages and not require every type to be implemented
in a seperate class, a `Bernard\Message\DefaultMessage` is provided. It can hold
any number of proberties and only needs a name for the message. The queue name
is then generated from that. When generating the queue name it will insert a "_"
before any uppercase letters and then lowercase everything.

Messages are serialized to json using [JMS Serializer](http://jmsyst.com/libs/serializer).
Therefore an instance of that is required. Also if custom message classes are
used it is needed to add metadata for being able to serialize and deserialize them.

``` php
<?php

use Bernard\Message\DefaultMessage;
use Bernard\Message\Envelope;
use Bernard\Producer;
use Bernard\QueueFactory\PersistentFactory;
use Bernard\Serializer\JMSSerializer;

// .. create serializer instance where src/Bernard/Resources/serializer
// is registered as a metadata dir with "Bernard" as prefix.
$serializer = new JMSSerializer($jmsSerializer);

// .. create connection
$factory = new PersistentFactory($connection, $serializer);
$producer = new Producer($factory);

$message = new DefaultMessage("SendNewsletter", array(
    'newsletterId' => 12,
));

$producer->produce($message);

// or give it to a queue directly
$factory->get('my-queue')->enqueue(new Envelope($message));
```

#### In Memory Queues

Bernard comes with an implemention for `SplQueue` which is completly in memory.
It is useful for development and/or testing, when you don't necessarily want actions to be
performed.

### Consuming Messages

A single message represents a job that needs to be performed, and as described
earlier, a message's name is used to determine which service object should
receive that message.

A service object can be any object that has a method corresponding to the message
name prefixed with `on`. So `new DefaultMessage('SendNewsletter')` will trigger a
call to `$serviceObject->onSendNewsletter`. For the system to know which service
object should handle which messages, your are required to register them first.

``` php
<?php

use Bernard\ServiceResolver\ObjectResolver;
use Bernard\Consumer;

// .. create connection and a queuefactory
// NewsletterMessageHandler is a pseudo service object that responds to
// onSendNewsletter.

$serviceResolver = new ObjectResolver;
$serviceResolver->register('SendNewsletter', new NewsletterMessageHandler);

// Bernard also comes with a service resolver for Pimple (Silex) which allows you
// to use service ids and have your service object lazy loader.
//
// $serviceResolver = new \Bernard\Pimple\PimpleAwareResolver($pimple);
// $serviceResolver->register('SendNewsletter', 'my.service.id');
//
// Symfony DependencyInjection component is also supported.
//
// $serviceResolver = new \Bernard\Symfony\ContainerAwareServiceResolver($container);
// $serviceResolver->register('SendNewsletter', 'my.service.id');

// Create a Consumer and start the loop. The second argument is optional and
// is the queue failed messages should be added to. The last argument (array) is also optional
// and the defaults can be seen in the Consumer class.
$consumer = new Consumer($serviceResolver);
$consumer->consume($queueFactory->get('send-newsletter'), $queueFactory->create('failed'), array(
    'max-runtime' => 900,
    'max-retries' => 5,
));
```

Bernard comes with a `ConsumeCommand` which can be used with Symfony Console 
component.

``` php
<?php

use Bernard\Symfony\Command\ConsumeCommand;

// create $console application
$console->add(new ConsumeCommand($services, $queueManager));
```

It can then be used as any other console command. The argument given should be
the queue that your messages are on. If we use the earlier example with sending
a newsletter, it would look like this.

``` bash
$ /path/to/console bernard:consume 'send-newsletter'
```

Integration with Frameworks
---------------------------

To make it easier to get started and have it "just work" with sending messages,
a number of integrations have been created.

* __Silex__: [BernardServiceProvider](https://github.com/henrikbjorn/BernardServiceProvider)

Monitoring
----------

Having a message queue where it is not possible to know what is in the queue and the
contents of the messages is not very handy, so for that there is [Juno](https://github.com/henrikbjorn/Juno).

It is implemented in Silex and is very lightweight. Also if needed, it can be
embedded in other Silex or Flint applications.

Alternatives
------------

If this is not your cup of tea there are other alternatives that might be
better suited to your needs.

* [php-resque](https://github.com/chrisboulton/php-resque)
* [Resque](https://github.com/defunkt/resque)

Special Thanks
--------------

* [Igor Wiedler](http://igor.io) and [Dave Marshall](http://davedevelopment.co.uk) for helping me find a better name
than Raekke.
* [Benjamin Eberlei](http://whitewashing.de) for advice regarding architeture
* [Peytz & Co](http://peytz.dk)
