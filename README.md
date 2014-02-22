<p align="center">
  <a href="http://bernard.rtfd.org">
    <img src="https://bernard.readthedocs.org/en/latest/_static/img/logo_small@2x.png" alt="Bernard" />
  </a>
</p>

Bernard makes it super easy and enjoyable to do background processing in PHP. It does this by utilizing queues and long running processes. It supports normal queueing drivers but also implements simple ones with Redis and Doctrine.

Currently theese are the supported backends, with more coming with each release:

 * Predis / PhpRedis
 * Amazon SQS
 * Iron MQ
 * Doctrine DBAL

You can learn more on our website about Bernard and its [releated projcets][website] or just dive directly into [the
documentation][documentation].

[![Build Status](https://travis-ci.org/bernardphp/bernard.png?branch=master)][travis]

[documentation]: http://bernardphp.com/docs/bernard
[website]: http://bernardphp.com/
[travis]: https://travis-ci.org/bernardphp/bernard
