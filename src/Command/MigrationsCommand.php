<?php

namespace Nalogka\ClickhouseMigrationsBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrationsCommand extends AbstractMigrationCommand
{
    private const COMMAND_SUCCESS = 0;
    protected static $defaultName = 'clickhouse:migrations:migrate';

    protected function configure()
    {
        $this->setDescription('Execute clickhouse migrations')
            ->addArgument(
                'direction',
                InputArgument::OPTIONAL,
                'The direction for migration (up or down) to migrate to.',
                'up'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $question = sprintf(
            'WARNING! You are about to execute a migration in database "%s" that could result in schema changes and data loss. Are you sure you wish to continue?',
            $this->migrationsManager->getClickhouseMigrationStorage()->getDatabaseName()
        );

        if (!$this->canExecute($question, $input)) {
            $this->io->error('Migration cancelled!');

            return 3;
        }
        $direction = $this->detectDirection($input->getArgument('direction'));

        if ($direction === 'up') {
            $migrationsForExecute = $this->migrationsManager->getNonAppliedMigrations();
            if (count($migrationsForExecute) === 0) {
                $this->io->write('No migrations to execute.');

                return 3;
            }
            $this->io->writeln('Will be execute migrations:');
            foreach ($migrationsForExecute as $migrationVersion) {
                $this->io->text(
                    sprintf(
                        '<comment>>></comment> %s',
                        $migrationVersion
                    )
                );
            }
        } else {
            $last = $this->migrationsManager->getLastAppliedMigration();
            if (!$last) {
                $this->io->error('There are no applied migrations');

                return 1;
            }
            $this->io->warning(
                sprintf('This migration will be down: %s', $this->migrationsManager->getLastAppliedMigration())
            );
        }

        $question = 'Are you sure you wish to continue?';

        if (!$this->canExecute($question, $input)) {
            $this->io->error('Migration cancelled!');

            return 3;
        }

        $version = $direction === 'up' ? $this->migrationsManager->upMigrations() : $this->migrationsManager->downMigration();
        $this->io->text(
            sprintf(
                '<comment>>></comment> Migration to version: %s',
                $version
            )
        );

        return self::COMMAND_SUCCESS;
    }

    private function detectDirection(?string $direction)
    {
        $available = ['up', 'down'];
        if (!in_array($direction, $available)) {
            throw new \RuntimeException(
                'Unknown migration direction: %s, available: %s',
                $direction,
                implode(',', $available)
            );
        }

        return $direction ?: 'up';
    }
}