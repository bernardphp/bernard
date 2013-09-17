<?php

namespace Bernard\Message;

/**
 * @package Bernard
 */
class DefaultMessage extends AbstractMessage
{
    protected $name;

    /**
     * @param string $name
     * @param array  $parameters
     */
    public function __construct($name, array $parameters = array())
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        $this->name = preg_replace('/(^([0-9]+))|([^[:alnum:]-_+])/i', '', $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }
}
