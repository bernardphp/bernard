<?php

namespace Bernard\Tests\Fixtures;

/**
 * This is a stub of the original PushTask class from Google
 * which allows intro spection.
 */
class PushTask
{
    static $messages = array();

    protected $url_path;
    protected $queury_data;
    protected $options;

    public function __construct($url_path, array $query_data = array(), array $options = array())
    {
        $this->url_path = $url_path;
        $this->query_data = $query_data;
        $this->options = $options;
    }

    public function add()
    {
        static::$messages[] = $this;
    }
}
