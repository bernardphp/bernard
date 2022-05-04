<?php

declare(strict_types=1);

namespace Bernard\Driver\Doctrine\Command;

use Bernard\Driver\Doctrine\MessagesSchema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends Command
{
    public function __construct($name)
    {
        parent::__construct('bernard:doctrine:'.$name);
    }

    /**
     * {@inheritdoc}
     */
    public function configure(): void
    {
        $this->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Output generated SQL statements instead of applying them');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): void
    {
        $schema = new Schema();
        MessagesSchema::create($schema);
        $sync = $this->getSynchronizer($this->getHelper('connection')->getConnection());

        if ($input->getOption('dump-sql')) {
            $output->writeln(implode(';'.\PHP_EOL, $this->getSql($sync, $schema)).';');

            return;
        }

        $output->writeln('<comment>ATTENTION</comment>: This operation should not be executed in a production environment.'.\PHP_EOL);
        $output->writeln('Applying database schema changes...');
        $this->applySql($sync, $schema);
        $output->writeln('Schema changes applied successfully!');
    }

    /**
     * @return \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer
     */
    protected function getSynchronizer(Connection $connection)
    {
        return new Synchronizer($connection);
    }

    /**
     * @return array
     */
    abstract protected function getSql(Synchronizer $sync, Schema $schema);

    abstract protected function applySql(Synchronizer $sync, Schema $schema);
}
