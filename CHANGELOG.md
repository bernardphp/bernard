Changelog
=========

0.9.0 / 2013-08-10
------------------

 * Support any callable in ObjectResolver
 * Implement Middleware. Middleware is used by the Consumer and Producer when a Message in queued or consumed.
 * Removed Spork support until it can be reimplemented as a Middleware.
 * Add `LoggerMiddleware` and `ErrorLogMiddleware` for basic logging when producing and consuming messages.

0.8.0 / 2013-08-01
------------------

 * Service resolvers now resolve to a callable. This allows for closures to do work.
 * Consumer is now responsible for creating Invoker object.
 * Spork return a Proxy object that allows calling the right method on service object.
 * New schema for `DoctrineDriver`. Queues are now kept in a seperate table.
 * `ObjectResolver` now supports object instances and class names. Laravel can then register 
 facades.
 * ServiceResolvers now takes an optional array of `array('MessageName' => $service)`.

0.7.1 / 2013-07-12
------------------

 * Fix bug in `DoctrineDriver` with prepared statements and limit placeholders.

0.7.0 / 2013-07-12
------------------

 * Add `ProduceCommand` by @ukautz.
 * Refactor examples in `example` directory to remove ugly code.
 * BC Break. Rename `Invocator` to `Invoker` as the former is not a word.
 * New `NaiveSerializer` with no dependencies.
 * Fixed error in `DoctrineDriver` wheen peeking.

0.6.1 / 2013-07-03
------------------

 * Increment sleep in drivers that does not natively support internal to minimize CPU usage.
 * Fix error in `$queueUrls` for SQS Driver where aliased queue urls would show up.
 * Include documentation for the new drivers and options. @ukautz

0.6.0 / 2013-07-03
------------------

 * Add driver for Amazon SQS @ukautz
 * Add driver for Iron MQ @ukautz
 * Add driver for Doctrine DBAL which brings support for major SQL backends.
 * Implement acknowledge logic for messages and drivers that uses it. @ukautz
 * Add prefetching for drivers that use slow endpoints and supports getting more than one message.
 * Refactor `Consumer` and cover it with tests.
 * Drop using mocks where appropiate and instead use `InMemoryQueue` and `InMemoryFactory`
 * Remove `example/in_memory.php`.
 * Bring consistency by using `Envelope` internally and `Message` externally (end user).
