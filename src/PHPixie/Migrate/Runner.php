<?php

namespace PHPixie\Migrate;

use PHPixie\Database\Connection;
use PHPixie\Slice\Data;

class Runner
{
    /**
     * @var Builder
     */
    protected $builder;

    public function createDatabase($connectionName)
    {
        $config = $this->config($connectionName);

        $adapter = $this->builder->adapter(
            $config->getRequired('adapter')
        );

        $adapter->createDatabase($config);
    }

    public function dropDatabase($connectionName)
    {
        $config = $this->config($connectionName);

        $adapter = $this->builder->adapter(
            $config->getRequired('adapter')
        );

        $adapter->dropDatabase($config);
    }

    public function migrate(Data $config)
    {
        $connection = $this->connection($config);

        $last = $connection->selectQuery()
            ->table('phpixieMigrations')
            ->limit(1)
            ->execute()
            ->getField('lastMigration');

        $last = !empty($last) ? $last[0] : null;
        $iterator = new \RecursiveDirectoryIterator(
            $config->getRequired('migrationsPath')
        );

        $namespace = $config->getRequired('namespace');

        foreach($iterator as $file) {
            $class = $file->getBasename('.php');
            $class = $namespace."\\".$class;

            $migration = new $class();
            $migration->run();
        }
    }

    /**
     * @param string $connectionName
     * @return Data
     */
    protected function config($connectionName)
    {
        $database = $this->builder->database();
        return $database->connectionConfig($connectionName);
    }

    /**
     * @param string $connectionName
     * @return Connection
     */
    protected function connection($connectionName)
    {
        return $this->builder->database()->connection($connectionName);
    }
}