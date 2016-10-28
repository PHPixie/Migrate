<?php

namespace PHPixie\Migrate\Adapters;

use PHPixie\Slice\Data;
use PDO;

abstract class Adapter
{
    protected $config;
    protected $defaultMigrationTable = '__migrate';
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    protected function getLastMigration()
    {
        $table = $this->requireMigrationTable();
        $result = $this->execute("SELECT lastMigration FROM $table")->fetchColumn();
        $last = empty($result) ? null : $result[0];
        return $last;
    }
    
    protected function setLastMigration($migration)
    {
        $table = $this->requireMigrationTable();
        $rows = $this->execute("SELECT lastMigration FROM $table")->fetchColumn();
        
        if(count($rows) == 0) {
            $this->execute("INSERT INTO $table VALUES('$migration')");
            return;
        }
        
        $this->execute("UPDATE $table SET lastMigration='$migration'");
    }
    
    public function execute($query)
    {
        return $this->pdo()->query($query);
    }
    
    public function pdo()
    {
        if($this->pdo === null) {
            $this->pdo = $this->buildPdo($this->dsn())
        }
    }
    
    public function disconnect()
    {
        $this->pdo = null;
    }
    
    protected function buildPdo($dsn)
    {
        new PDO(
            $dsn,
            $this->config->get('user',''),
            $this->config->get('password', ''),
            $this->config->get('connectionOptions', array())
        );
            
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    
    abstract public function dsn();
    abstract public function dropDatabase()
    abstract public function createDatabase();
}