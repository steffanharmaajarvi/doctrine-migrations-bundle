<?php

namespace Nalogka\ClickhouseMigrationsBundle\Migrations;

use ClickHouseDB\Statement;
use Nalogka\ClickhouseMigrationsBundle\Service\ClickhouseMigrationStorage;

class MigrationsManager
{
    /**
     * @var ClickhouseMigrationStorage
     */
    private $clickhouseMigrationStorage;
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(ClickhouseMigrationStorage $ClickhouseMigrationStorage, Configuration $configuration)
    {
        $this->clickhouseMigrationStorage = $ClickhouseMigrationStorage;
        $this->configuration = $configuration;
        $this->initialize();
    }

    /**
     * @return string last migration version
     * @throws \Exception
     */
    public function upMigrations(): string
    {
        $migrations = $this->getNonAppliedMigrations();
        foreach ($migrations as $version) {
            $this->upMigration($version);
        }

        return $version;
    }

    /**
     * @return string|null previous migration version or null if down only one last migration
     */
    public function downMigration(): ?string
    {
        $version = $this->getLastAppliedMigration();
        $migration = $this->getMigrationObject($version);
        $migration->down();
        $this->removeMigration($version);

        return $this->getLastAppliedMigration();
    }

    /**
     * @return array|string[]
     */
    public function getNonAppliedMigrations(): array
    {
        return array_diff($this->getMigrationsList(), $this->getAppliedMigrations());
    }

    /**
     * @return string|null
     */
    public function getLastAppliedMigration(): ?string
    {
        $appliedMigrations = $this->getAppliedMigrations();

        return $appliedMigrations ? array_pop($appliedMigrations) : null;
    }

    /**
     * @return array|string[]
     */
    public function getAppliedMigrations(): array
    {
        $appliedVersions = $this->getClickhouseMigrationStorage()->selectAllAppliedMigrations();

        return array_column($appliedVersions, 'version');
    }

    /**
     * @return string[]
     */
    public function getMigrationsList(): array
    {
        $files = scandir($this->configuration->migrationsPath);

        $migrations = [];

        foreach ($files as $file) {
            if (!preg_match('/(^Version)(\d{14})(.php)/', $file, $matches)) {
                continue;
            }

            $migrationDate = \DateTimeImmutable::createFromFormat('YmdHis', $matches[2]);

            $migrations[] = [
                'version' => $matches[2],
                'timestamp' => $migrationDate->getTimestamp(),
            ];
        }

        usort(
            $migrations,
            function ($a, $b) {
                return $a['timestamp'] <=> $b['timestamp'];
            }
        );

        return array_column($migrations, 'version');
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @return ClickhouseMigrationStorage
     */
    public function getClickhouseMigrationStorage(): ClickhouseMigrationStorage
    {
        return $this->clickhouseMigrationStorage;
    }

    /**
     *
     * @param string $version
     * @return Statement
     */
    protected function removeMigration(string $version): Statement
    {
        return $this->clickhouseMigrationStorage->deleteMigration($version);
    }

    /**
     *
     * @param string $version
     * @throws \Exception
     */
    protected function addMigration(string $version): void
    {
        $this->getClickhouseMigrationStorage()->insertMigration(
            [
                'version' => $version,
                'apply_time' => new \DateTimeImmutable(),
            ]
        );
    }

    /**
     * @param string $version
     * @return AbstractMigration
     */
    protected function getMigrationObject(string $version): AbstractMigration
    {
        $className = $this->getClassReferenceByVersion($version);
        $filePath = $this->getFilePathByVersion($version);
        if (!file_exists($filePath)) {
            throw new \RuntimeException("Not found migration file: {$filePath}");
        }
        require_once $filePath;
        if (!class_exists($className)) {
            throw new \RuntimeException("Not found migration class {$className} in {$filePath}");
        }

        return new $className($this->getClickhouseMigrationStorage()->getClient());
    }

    /**
     * @param string $version
     * @throws \Exception
     */
    protected function upMigration(string $version): void
    {
        $migration = $this->getMigrationObject($version);
        $migration->up();
        $this->addMigration($version);
    }

    /**
     * Create migration table and migration dir if not exist
     */
    private function initialize(): void
    {
        $this->createDirIfNotExists($this->configuration->migrationsPath);
        $this->getClickhouseMigrationStorage()->createMigrationTable();
    }

    private function createDirIfNotExists(string $dir) : void
    {
        if (file_exists($dir)) {
            return;
        }

        mkdir($dir, 0755, true);
    }

    /**
     * @param string $version
     * @return string
     */
    private function getFilePathByVersion(string $version): string
    {
        $fileName = sprintf('Version%d.php', $version);
        $migrationsDir = rtrim($this->configuration->migrationsPath, "/");

        return "{$migrationsDir}/{$fileName}";
    }

    /**
     * @param string $version
     * @return string
     */
    private function getClassReferenceByVersion(string $version): string
    {
        return sprintf('%s\\Version%d', $this->configuration->migrationsNamespace, $version);
    }
}