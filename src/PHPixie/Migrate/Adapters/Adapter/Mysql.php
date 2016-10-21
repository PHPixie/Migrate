<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;
use PHPixie\Slice\Data;

class Mysql extends Adapter
{
    public function dropDatabase(Data $config)
    {
        $pdo = $this->buildPdo(
            $this->buildDsn($config),
            $config
        );

        $database = $config->getRequired('database');
        $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    }

    public function createDatabase(Data $config)
    {
        $pdo = $this->buildPdo(
            $this->buildDsn($config),
            $config
        );

        $database = $config->getRequired('database');
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
        $pdo->exec("USE `$database`");
        $pdo->exec("CREATE TABLE IF NOT EXISTS phpixieMigrations(
            lastMigration VARCHAR(255)
        )");
    }

    /**
     * @param Data $config
     * @return string
     */
    protected function buildDsn(Data $config)
    {
        return 'mysql:host='.$config->get('host', 'localhost');
    }
}