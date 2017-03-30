<?php

namespace Bernard\Tests\Command\Doctrine;

class CreateCommandTest extends BaseCommandTest
{
    /**
     * {@inheritdoc}
     */
    public function getShortClassName()
    {
        return 'CreateCommand';
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlMethod()
    {
        return 'getCreateSchema';
    }

    /**
     * {@inheritdoc}
     */
    public function applySqlMethod()
    {
        return 'createSchema';
    }
}
