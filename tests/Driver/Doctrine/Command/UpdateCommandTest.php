<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine\Command;

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
