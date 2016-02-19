<?php

namespace Bernard\Driver;

use Bernard\Driver;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractDriver implements Driver
{
    /**
     * Validate queue options
     *
     * @param array $options
     *
     * @return array
     * @throws InvalidOptionsException
     */
    final public function validateQueueOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configureQueueOptions($resolver);

        return $resolver->resolve($options);
    }

    /**
     * Validate queue options
     *
     * @param array $options
     *
     * @return array
     * @throws InvalidOptionsException
     */
    final public function validatePushOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $this->configurePushOptions($resolver);

        return $resolver->resolve($options);
    }

    /**
     * Configure createQueue options
     *
     * @param OptionsResolver $resolver
     */
    public function configureQueueOptions(OptionsResolver $resolver)
    {
    }

    /**
     * Configure push message options
     *
     * @param OptionsResolver $resolver
     */
    public function configurePushOptions(OptionsResolver $resolver)
    {
    }
}
