<?php

namespace Nalogka\ClickhouseMigrationsBundle\Command;


use Nalogka\ClickhouseMigrationsBundle\Migrations\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsGenerateCommand extends AbstractMigrationCommand
{
    private const COMMAND_SUCCESS = 0;
    protected static $defaultName = 'clickhouse:migrations:generate';

    protected function configure()
    {
        $this->setDescription('Generate new empty clickhouse migration');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $generator = new Generator($this->migrationsManager->getConfiguration());
        $path = $generator->generateMigration();
        $this->io->write(sprintf('Success generate new Migration: %s', $path));

        return self::COMMAND_SUCCESS;
    }

}