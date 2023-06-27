<p align="center">
  <a href="http://bernard.rtfd.org">
    <img src="https://bernard.readthedocs.io/_static/img/logo_small@2x.png" alt="Bernard" />
  </a>
</p>

[![Latest Version](https://img.shields.io/github/release/bernardphp/bernard.svg?style=flat-square)](https://github.com/bernardphp/bernard/releases)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg?style=flat-square)](https://php.net/)
[![GitHub Workflow Status](https://img.shields.io/github/workflow/status/bernardphp/bernard/CI?style=flat-square)](https://github.com/bernardphp/bernard/actions?query=workflow%3ACI)
[![Total Downloads](https://img.shields.io/packagist/dt/bernard/bernard.svg?style=flat-square)](https://packagist.org/packages/bernard/bernard)

Bernard makes it super easy and enjoyable to do background processing in PHP.
It does this by utilizing queues and long running processes.
It supports normal queueing drivers but also implements simple ones with Redis and Doctrine.

Currently these are the supported backends, with more coming with each release:

- Predis / PhpRedis
- Amazon SQS
- Iron MQ
- Doctrine DBAL
- Pheanstalk
- PhpAmqp / RabbitMQ
- Queue interop


## Install

Via Composer

```bash
$ composer require bernard/bernard
```


## Documentation

Please see the [official documentation](https://bernard.readthedocs.org).


## Testing

We try to follow BDD and TDD, as such we use both [phpspec](http://www.phpspec.net) and [phpunit](https://phpunit.de) to test this library.

```bash
$ composer test
```

You can run the functional tests by executing:

```bash
$ composer test-functional
```


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
