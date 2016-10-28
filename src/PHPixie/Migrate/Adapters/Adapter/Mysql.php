<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Mysql extends Adapter
{
    public function dropDatabase()
    {
        $this->disconnect();
        $pdo = $this->buildPdo($this->dsn(false));

        $database = $this->config->getRequired('database');
        $pdo->exec("DROP DATABASE IF EXISTS `$database`");
    }

    public function createDatabase()
    {
        $this->disconnect();
        $pdo = $this->buildPdo($this->dsn(false));

        $database = $this->config->getRequired('database');
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database`");
    }

    public function dsn($withDatabase = true)
    {
        $dsn = 'mysql:';
        if($socket = $this->config->get('unixSocket')) {
            $dsn.='unix_socket='.$socket;
        } else {
            $dsn.='host='.$this->config->get('host', 'localhost');
            $dsn.=';port='.$this->config->get('port', '3306');
        }
        
        if($charset = $this->config->get('charset')) {
            $dsn.=';charset='.$charset;
        }
        
        if($withDatabase) {
            $dsn.=';dbname='.$this->config->getRequired('database');
        }
        
        return $dsn;
    }
    
    protected function requireMigrationTable();
    {
        $table = $this->config->get('table', $this->defaultMigrationTable);
        
        $this->execute("CREATE TABLE IF NOT EXISTS $table(
            lastMigration VARCHAR(255)
        )");
        
        return $table;
    }
}