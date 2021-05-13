<?php

namespace Nalogka\ClickhouseMigrationsBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ClickhouseMigrationsExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('clickhouse_migrations.configuration');
        $definition->replaceArgument('$tableName', $config['table_name']);
        $definition->replaceArgument('$migrationsPath', $config['migrations_path']);
        $definition->replaceArgument('$migrationsNamespace', $config['migrations_namespace']);
    }
}