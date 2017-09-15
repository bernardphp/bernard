<p align="center">
  <a href="http://bernard.rtfd.org">
    <img src="https://bernard.readthedocs.io/_static/img/logo_small@2x.png" alt="Bernard" />
  </a>
</p>

Bernard makes it super easy and enjoyable to do background processing in PHP. It does this by utilizing queues and long running processes. It supports normal queueing drivers but also implements simple ones with Redis and Doctrine.

Currently these are the supported backends, with more coming with each release:

 * Predis / PhpRedis
 * Amazon SQS
 * Iron MQ
 * Doctrine DBAL
 * Pheanstalk
 * PhpAmqp / RabbitMQ
 * Queue interop

You can learn more on our website about Bernard and its [related projects][website] or just dive directly into [the
documentation][documentation].

[![Build Status](https://travis-ci.org/bernardphp/bernard.png?branch=master)][travis] [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/bernardphp/bernard/badges/quality-score.png?s=f752c78d347624081f5b6d3d818fe14eef0311c2)](https://scrutinizer-ci.com/g/bernardphp/bernard/)

[documentation]: https://bernard.readthedocs.org
[website]: http://bernardphp-com.rtfd.org
[travis]: https://travis-ci.org/bernardphp/bernard
