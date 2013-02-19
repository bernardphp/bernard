Raekke
======

Raekke is a message queue implemented in php. It is very similiar to Resque and allows for easy creation of workers
and creating distributed systems.

Getting Started
---------------

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

### Sending Messages

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

$manager->push($message);

// or get the queue and specify the queue name freely
$queue = $manager->get('custom-queue');
$queue->push($message);
```

### Working on Messages

A single message represents a job that needs to be performed. And as described earlier a message's name is used
to determaine what worker should have that message. For that a worker manager is needed.

``` php
<?php

use Raekke\WorkerManager;

// create a $queueManager instance.

$workerManager = new WorkerManager($queueManager);
```

The worker manager also needs to know what workers it can send messages to. A worker is an object or a closure.
If it is an object and the worker is registered to `SendNewsletter` the manager will call the method
`$workerService->onSendNewsletter`. This allows for a WorkerService to handle more than one job.

``` php
<?php

class NewsletterWorker
{
    public function onSendNewsletter(DefaultMessage $message)
    {
        // Do some work on DefaultMessage here.
    }
}

$workerManager->register('SendNewsletter', new NewsletterWorker);

// or register multiple services at once.
$workerManager->registerServices(array(
    'SendNewsletter' => new NewsletterWorker(),
));
```

The worker manager would normally be abstracted out and populated by a container of some sort like the [Symfony Dependency
Injection](http://symfony.com/doc/current/components/dependency_injection).

Anyone who have created a deamon in php and tried handling signal's they know it is hard. Therefor Raekke comes with a
worker command for [Symfony Console](http://symfony.com/doc/current/components/console) component. The command should
be added to your console application.

``` php
<?php

use Raekke\Command\WorkerCommand;

// .. create an instance of Symfony\Console\Application as $app
// .. create a Raekke\WorkerManager as $workerManager
$app->add(new WorkerCommand($workerManager));
```

It can then be used as any other console command. The argument given should be the queue that your messages is on.
If we use the earlier example with sending newsletter it would look like this.

``` bash
$ /path/to/console raekke:worker 'send-newsletter'
```

Integration with Frameworks
---------------------------

To make it easier to start up and have it "just work" with sending messages a number of integrations have
been created.

* Somebody should do this part...

Monitoring
----------

Having a message queue where it is not possible to what whats in queue and the contents of the messages is not
very handy. And for that there is [Juno](https://github.com/henrikbjorn/Juno).

It is implemented in Silex and is very lightweight. Also if needed it can be embedded in other Silex or Flint
applications.

![Juno](http://i.imgur.com/oZFzfKq.png)

Alternatives
------------

If this is not your cup of tea there exists other alternatives that might be better for your needs.

* [php-resque](https://github.com/chrisboulton/php-resque)
* [Resque](https://github.com/defunkt/resque)


Happy Customers
---------------

Not really anybody as it is not completly finished. If you need something like this i encourage you to contact
me and we will figure out how we can work together on this.
