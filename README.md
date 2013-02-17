Raekke
======

Raekke is a message queue implemented with PHP and Redis. Redis provides a fast backend for our queues also because Resque
in the Ruby community is widely used at work we can share some infrastructure (when complete).

Proposed workflow
-----------------

``` php
<?php

// $c is a container

$message = new Message('Import', array(
    'path' => tmpnam('prefix', sys_get_temp_dir()),
));

// The queue name will be determained from the getMessageName() method.
$c['queue_manager']->enqueue($message);
```
