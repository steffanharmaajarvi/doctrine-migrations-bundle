<?php

namespace Nalogka\ClickhouseMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('clickhouse_migrations');
        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('table_name')->defaultValue('migrations_versions')->end()
            ->scalarNode('migrations_path')->defaultValue('%kernel.project_dir%/clickhouse_migrations')->end()
            ->scalarNode('migrations_namespace')->defaultValue('ClickhouseMigrations')->end()
            ->end();

        return $treeBuilder;
    }
}
