Changelog
=========

0.7.0 / yyyy-mm-dd
------------------

 * BC Break. Rename `Invocator` to `Invoker` as the former is not a word.

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
