<?php

namespace Nalogka\ClickhouseMigrationsBundle\Service;

use ClickHouseDB\Client as ClickHouseClient;
use ClickHouseDB\Statement;
use Nalogka\ClickhouseMigrationsBundle\Migrations\Configuration;

/**
 * Class for interaction with clickhouse db migration storage
 */
class ClickhouseMigrationStorage
{
    /**
     * @var ClickHouseClient
     */
    private $client;
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(ClickHouseClient $client, Configuration $configuration)
    {
        $this->client = $client;
        $this->configuration = $configuration;
    }

    public function getDatabaseName()
    {
        return $this->client->settings()->getDatabase();
    }

    /**
     * @return array
     */
    public function selectAllAppliedMigrations()
    {
        $sql = sprintf('SELECT m.version FROM %s m ORDER BY apply_time', $this->getTableName());
        $statement = $this->client->select($sql);

        return $statement->rows();
    }

    public function insertMigration($migrationData)
    {
        $this->client->insert($this->getTableName(), [$migrationData]);
    }

    /**
     *
     * @param string $version
     * @return Statement
     */
    public function deleteMigration(string $version): Statement
    {
        return $this->client->write(
            sprintf("ALTER TABLE %s DELETE WHERE version ='%s'", $this->getTableName(), $version)
        );
    }

    public function createMigrationTable()
    {
        return $this->client->write(
            sprintf(
                'CREATE TABLE IF NOT EXISTS %s (
                version String,
                apply_time DateTime DEFAULT NOW()
            ) ENGINE = ReplacingMergeTree()
                ORDER BY (version)',
                $this->getTableName()
            )
        );

    }

    public function getClient()
    {
        return $this->client;
    }

    private function getTableName()
    {
        return $this->configuration->tableName;
    }

}