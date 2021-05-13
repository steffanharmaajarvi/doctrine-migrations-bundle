<?php

namespace Nalogka\ClickhouseMigrationsBundle\Command;

use Nalogka\ClickhouseMigrationsBundle\Migrations\MigrationsManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractMigrationCommand extends Command
{
    /**
     * @var MigrationsManager
     */
    protected $migrationsManager;
    /**
     * @var SymfonyStyle
     */
    protected $io;

    public function __construct(MigrationsManager $migrationsManager)
    {
        $this->migrationsManager = $migrationsManager;
        parent::__construct();
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function canExecute(string $question, InputInterface $input): bool
    {
        return !$input->isInteractive() || $this->io->confirm($question);
    }
}