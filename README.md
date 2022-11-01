ClickhouseMigrationsBundle
========================

This bundle added the migrations system for [clickhouse type Databases](https://clickhouse.tech/docs/)
into Symfony applications. Database migrations help you version the changes in
your database schema and apply them in a predictable way on every server running
the application.

## Install

```
composer require dmamontov/clickhouse-migrations-bundle
```
## Clickhouse connection

For use this bundle you need set clickhouse Client as Symfony service.
As example:
```yaml
#config/services.yaml
services:
    ClickHouseDB\Client:
        arguments:
            $connectParams:
                host: 'http://localhost'
                port: 8123
                username: 'username'
                password: ''
                sslCA: 'path_to_ssl_cert'
            $settings:
                database: 'default'
```
[More details about ClickHouseDB\Client settings](https://github.com/smi2/phpClickHouse).

## Settings

If you need set migrations, set clickhouse migrations configuration.
But this is not necessary, these settings are set by default:
```yaml
#config/packages/clickhouse_migrations.yaml
clickhouse_migrations:
    table_name: 'migrations_versions'
    migrations_path: '%kernel.project_dir%/clickhouse_migrations'
    migrations_namespace: 'ClickhouseMigrations'
```

## Using
Generate new migration class:
```bash
bin/console clickhouse:migrations:generate
```

Execute all unapplied migrations up:
```bash
bin/console clickhouse:migrations:migrate
```

One last migration down:
```bash
bin/console clickhouse:migrations:migrate down
```
