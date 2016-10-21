<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;
use PHPixie\Slice\Data;

class Sqlite extends Adapter
{
    public function dropDatabase(Data $config)
    {
        $file = $config->getRequired('file');
        unlink($file);
    }

    public function createDatabase(Data $config)
    {
        $pdo = $this->buildPdo(
            'sqlite:'.$config->getRequired('file'),
            $config
        );

        $pdo->exec("CREATE TABLE IF NOT EXISTS phpixieMigrations(
            lastMigration VARCHAR(255)
        )");
    }
}