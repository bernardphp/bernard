<?php

namespace Bernard\Message;

use Bernard\Message;
use Bernard\Util;

/**
 * @package Bernard
 */
abstract class AbstractMessage implements Message
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $class = get_class($this);

        if (substr($class, -7) == 'Message') {
            $class = substr($class, 0, -7);
        }

        return current(array_reverse(explode('\\', $class)));
    }

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return Util::guessQueue($this);
    }
}
