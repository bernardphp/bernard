<?php

namespace Raekke\Util;

/**
 * @package Raekke
 */
class ArrayCollection extends \Doctrine\Common\Collections\ArrayCollection
{
    /**
     * @param mixed $key
     * @param mixed $default
     */
    public function get($key, $default = null)
    {
        if (!$this->containsKey($key)) {
            return $default;
        }

        return parent::get($key);
    }
}
