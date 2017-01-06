<?php

namespace PHPixie\Migrate\Adapters;

use PHPixie\Slice\Data;
use PDO;

abstract class Adapter
{
    protected $connection;
    
    protected $quoteCharacter;
    
    public function __construct($connection)
    {
        $this->connection = $connection;
    }
    
    protected function quote($str)
    {
        return $this->quoteCharacter.$str.$this->quoteCharacter;
    }
    
    public function getLastMigration($table, $field)
    {
        $this->requireMigrationTable($table, $field);
        
        $lastMigration = $this->connection->selectQuery()
            ->table($table)
            ->fields(array($field))
            ->execute()
            ->getField($field);
        
        $last = empty($lastMigration) ? null : $lastMigration[0];
        return $last;
    }
    
    public function setLastMigration($table, $field, $migration)
    {
        $this->requireMigrationTable($table, $field);
        
        $lastMigration = $this->connection->selectQuery()
            ->table($table)
            ->fields(array($field))
            ->execute()
            ->getField($field);
        
        if(count($lastMigration) === 0) {
            $this->connection->insertQuery()
                ->table($table)
                ->data(array(
                    $field => $migration
                ))
                ->execute();
            return;
        }
        
        $this->connection->updateQuery()
            ->table($table)
            ->set(array(
                $field => $migration
            ))
            ->execute();
    }
    
    public function truncateTable($table)
    {
        $this->execute('TRUNCATE TABLE '.$this->quote($table));
    }
    
    public function isTableEmpty($table)
    {
        $count = $this->connection->countQuery()
            ->table($table)
            ->execute();
        
        return $count == 0;
    }
    
    public function insertData($table, $data) {
        foreach($data as $row) {
            $this->connection->insertQuery()
                ->table($table)
                ->data($row)
                ->execute();
        }
    }
    
    public function execute($query, $params = array())
    {
        return $this->connection->execute($query, $params);
    }
    
    public function dropDatabase()
    {
        $this->connection->disconnect();
        $pdo = $this->connection->buildPdo(false);
        
        $config = $this->connection->config();
        $database = $config->getRequired('database');
        
        $pdo->exec('DROP DATABASE IF EXISTS '.$this->quote($database));
    }
    
    public function createDatabase()
    {
        $this->connection->disconnect();
        $pdo = $this->connection->buildPdo(false);
        
        $config = $this->connection->config();
        $database = $config->getRequired('database');
        
        $pdo->exec('CREATE DATABASE IF NOT EXISTS '.$this->quote($database));
    }
    
    protected function requireMigrationTable($table, $field)
    {
        $this->execute(sprintf("CREATE TABLE IF NOT EXISTS %s(
                %s VARCHAR(255)
            )",
            $this->quote($table),
            $this->quote($field)
        ));
        
        return $table;
    }
    
    public function connection()
    {
        return $this->connection;
    }

    public function disableForeignKeyCheck()
    {

    }

    public function enableForeignKeyCheck()
    {

    }
}