<?php

declare(strict_types=1);

namespace Bernard\Tests\Driver\Doctrine\Command;

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
