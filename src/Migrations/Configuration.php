<?php
declare(strict_types=1);

namespace Nalogka\ClickhouseMigrationsBundle\Migrations;


class Configuration
{
    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $migrationsPath;

    /**
     * @var string
     */
    public $migrationsNamespace;

    public function __construct(string $tableName, string $migrationsPath, string $migrationsNamespace)
    {
        $this->tableName = $tableName;
        $this->migrationsPath = $migrationsPath;
        $this->migrationsNamespace = $migrationsNamespace;
    }

}