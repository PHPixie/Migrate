<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Pgsql extends Adapter
{
    public function dropDatabase()
    {
        $this->disconnect();
        $pdo = $this->buildPdo($this->dsn(false));

        $database = $this->config->getRequired('database');
        $pdo->exec("DROP DATABASE IF EXISTS \"$database\"");
    }

    public function createDatabase()
    {
        $this->disconnect();
        $pdo = $this->buildPdo($this->dsn(false));

        $database = $this->config->getRequired('database');
        $pdo->exec("CREATE DATABASE IF NOT EXISTS \"$database\"");
    }

    public function dsn($withDatabase = true)
    {
        $dsn = 'pgsql:';
        
        $dsn.='host='.$this->config->get('host', 'localhost');
        $dsn.=';port='.$this->config->get('port', '3306');
        
        if($withDatabase) {
            $dsn.=';dbname='.$this->config->getRequired('database');
        }
        
        return $dsn;
    }
    
    protected function requireMigrationTable();
    {
        $table = $this->config->get('table', $this->defaultMigrationTable);
;
        $pdo->exec("CREATE TABLE IF NOT EXISTS $table(
            lastMigration VARCHAR(255)
        )");
        
        return $table;
    }
}