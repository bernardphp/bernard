<?php

declare(strict_types=1);

namespace Bernard\Message;

/**
 * Apply this trait to your message when you want it to follow the default queue naming pattern.
 */
trait HasQueue
{
    /**
     * Identical to Bernard\Message::getName.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    public function getQueue()
    {
        return trim(strtolower(preg_replace('/[A-Z]/', '-\\0', $this->getName())), '-');
    }
}
