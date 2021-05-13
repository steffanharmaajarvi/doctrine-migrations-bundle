<?php

declare(strict_types=1);

namespace Nalogka\ClickhouseMigrationsBundle\Migrations;


use ClickHouseDB\Client;

abstract class AbstractMigration
{
    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public abstract function up(): void;

    public abstract function down(): void;
}