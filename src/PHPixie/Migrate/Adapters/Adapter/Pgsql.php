<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Pgsql extends Adapter
{
    protected $quoteCharacter = '"';
    
    public function createDatabase()
    {
        $this->connection->disconnect();
        $pdo = $this->connection->buildPdo(false);
        
        $config = $this->connection->config();
        $database = $config->getRequired('database');
        
        $pdo->exec('CREATE DATABASE '.$this->quote($database));
    }
}