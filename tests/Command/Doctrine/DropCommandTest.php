<?php

namespace Bernard\Tests\Command\Doctrine;

class DropCommandTest extends BaseCommandTest
{
    /**
     * {@inheritdoc}
     */
    public function getShortClassName()
    {
        return 'DropCommand';
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlMethod()
    {
        return 'getDropSchema';
    }

    /**
     * {@inheritdoc}
     */
    public function applySqlMethod()
    {
        return 'dropSchema';
    }
}
