<?php

namespace Bernard\Tests\Fixtures;

/**
 * This is a stub of the original PushTask class from Google
 * which allows introspection.
 */
class PushTask
{
    public static $messages = [];

    protected $url_path;
    protected $query_data;
    protected $options;

    public function __construct($url_path, array $query_data = [], array $options = [])
    {
        $this->url_path = $url_path;
        $this->query_data = $query_data;
        $this->options = $options;
    }

    public function add($queueName = 'default')
    {
        static::$messages[$queueName][] = $this;
    }
}
