Changelog
=========

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
