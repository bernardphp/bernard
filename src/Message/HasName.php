<?php

declare(strict_types=1);

namespace Bernard\Message;

/**
 * Apply this trait to your message when you want it to follow the default message naming pattern.
 */
trait HasName
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        $class = static::class;

        if (substr($class, -7) == 'Message') {
            $class = substr($class, 0, -7);
        }

        return current(array_reverse(explode('\\', $class)));
    }
}
