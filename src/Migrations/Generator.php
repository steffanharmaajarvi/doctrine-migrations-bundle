<?php

namespace Nalogka\ClickhouseMigrationsBundle\Migrations;

class Generator
{
    private const MIGRATION_TEMPLATE = <<<'TEMPLATE'
<?php

declare(strict_types=1);

namespace <namespace>;

use <AstractMigrationNamespace>;

final class Version<version> extends AbstractMigration
{
    public function up() : void
    {
    }
    
    public function down() : void
    {
    }
}

TEMPLATE;

    private $configuration;

    /**
     * Generator constructor.
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function generateMigration($version = null)
    {
        if ($version === null) {
            $version = date("YmdHis");
        }

        $placeHolders = [
            '<AstractMigrationNamespace>',
            '<namespace>',
            '<version>',
        ];

        $replacements = [
            __NAMESPACE__ . '\\AbstractMigration',
            $this->configuration->migrationsNamespace,
            $version,
        ];

        $code = str_replace($placeHolders, $replacements, self::MIGRATION_TEMPLATE);
        $code = preg_replace('/^ +$/m', '', $code);

        $fileName = "Version{$version}.php";
        $migrationsDir = rtrim($this->configuration->migrationsPath, "/");

        $path = "$migrationsDir/$fileName";

        file_put_contents($path, $code);

        return $path;
    }
}
