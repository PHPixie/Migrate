<?php

namespace PHPixie\Migrate;

class Migrator
{
    protected $builder;
    protected $adapter;
    
    protected $migrationTable;
    protected $lastMigrationField;
    protected $path;
    
    protected $allowedExtensions = array('php', 'sql');
    
    public function __construct($builder, $adapter, $config)
    {
        $this->builder = $builder;
        $this->adapter = $adapter;
        
        $this->migrationTable = $config->get('migrationTable', '__migrate');
        $this->lastMigrationField = $config->get('lastMigrationField', 'lastMigration');
        $this->path = $config->getRequired('path');
    }
    
    public function createDatabase()
    {
        $this->adapter->createDatabase();
    }
    
    public function dropDatabase()
    {
        $this->adapter->dropDatabase();
    }
    
    public function lastMigration()
    {
        return $this->adapter->getLastMigration($this->migrationTable, $this->lastMigrationField);
    }
    
    public function pendingMigrations()
    {
        $files = $this->builder->files()->getFileMap($this->path);
        
        uksort($files, function($a, $b) {
            return strnatcmp($a, $b);
        });
        
        $lastMigration = $this->lastMigration();
        $pending = array();
        
        foreach($files as $name => $file) {
            if(strnatcmp($name, $lastMigration) > 0) {
                $pending[$name] = $file;
            }
        }
        
        return $pending;
    }
    
    
    public function migrate($output)
    {
        $migrations = $this->pendingMigrations();
        $runner = $this->builder->runner($this->adapter, $output);
        
        foreach($migrations as $name => $file) {
            $runner->run($file);
            $this->adapter->setLastMigration($this->migrationTable, $this->lastMigrationField, $name);
        }
        
        return array_keys($migrations);
    }
}