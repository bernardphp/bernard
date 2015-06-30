<?php

namespace Bernard\Command\Doctrine;

use Bernard\Doctrine\MessagesSchema;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer as Synchronizer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package Bernard
 */
abstract class AbstractCommand extends Command
{
    public function __construct($name)
    {
        parent::__construct('bernard:doctrine:' . $name);
    }

    /**
     * {@inheritDoc}
     */
    public function configure()
    {
        $this->addOption('dump-sql', null, InputOption::VALUE_NONE, 'Output generated SQL statements instead of applying them');
    }

    /**
     * {@inheritDoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $schema = new Schema;
        MessagesSchema::create($schema);
        $sync = $this->getSynchronizer($this->getHelper('connection')->getConnection());

        if ($input->getOption('dump-sql')) {
            $output->writeln(implode(';' . PHP_EOL, $this->getSql($sync, $schema)) . ';');
            return;
        }

        $output->writeln('<comment>ATTENTION</comment>: This operation should not be executed in a production environment.' . PHP_EOL);
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
     * @param \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer $sync
     * @param \Doctrine\DBAL\Schema\Schema $schema
     * @return array
     */
    abstract protected function getSql(Synchronizer $sync, Schema $schema);

    /**
     * @param \Doctrine\DBAL\Schema\Synchronizer\SingleDatabaseSynchronizer $sync
     * @param \Doctrine\DBAL\Schema\Schema $schema
     */
    abstract protected function applySql(Synchronizer $sync, Schema $schema);
}
