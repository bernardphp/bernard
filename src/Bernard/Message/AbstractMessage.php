<?php

namespace Bernard\Message;

/**
 * @package Bernard
 */
abstract class AbstractMessage implements \Bernard\Message
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        $class = get_class($this);

        if (substr($class, -7) == 'Message') {
            $class = substr($class, 0, -7);
        }

        return current(array_reverse(explode('\\', $class)));
    }
}
