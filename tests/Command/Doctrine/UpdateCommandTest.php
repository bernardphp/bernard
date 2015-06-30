<?php

namespace Bernard\Tests\Command\Doctrine;

class UpdateCommandTest extends BaseCommandTest
{
    /**
     * {@inheritdoc}
     */
    public function getShortClassName()
    {
        return 'UpdateCommand';
    }

    /**
     * {@inheritdoc}
     */
    public function getSqlMethod()
    {
        return 'getUpdateSchema';
    }

    /**
     * {@inheritdoc}
     */
    public function applySqlMethod()
    {
        return 'updateSchema';
    }
}
