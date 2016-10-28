<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;
use PHPixie\Slice\Data;

class Sqlite extends Adapter
{
    public function dropDatabase(Data $config)
    {
        $this->disconnect();
        $file = $this->config->getRequired('file');
        file_put_contents($file, '');
    }

    public function createDatabase(Data $config)
    {
        $this->execute("CREATE TABLE IF NOT EXISTS phpixieMigrations(
            lastMigration VARCHAR(255)
        )");
    }
    
    protected function dsn($withDatabase = true)
    {
        $dsn = 'sqlite:'.$this->config->getRequired('file');
        return $dsn;
    }
}