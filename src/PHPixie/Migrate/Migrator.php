<?php

namespace PHPixie\Migrate;

class Migrator
{
    protected $builder;
    protected $adapter;
    protected $config;
    
    protected $allowedExtensions = array('php', 'sql');
    
    public function __construct($builder, $adapter, $config)
    {
        $this->builder = $builder;
        $this->adapter = $adapter;
        $this->config = $config;
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
        return $this->adapter->getLastMigration();
    }
    
    public function pendingMigrations()
    {
        $path = $this->config->getRequired('path');
        
        $files = $this->builder->files()->getFileMap($path);
        
        uksort($files, function($a, $b) {
            return strcmp(strtolower($a), strtolower($b));
        });
        
        $lastMigration = $this->lastMigration();
        $pending = array();
        
        foreach($files as $name => $file) {
            if(strcmp($name, strtolower($lastMigration)) > 0) {
                $pending[$name] = $file;
            }
        }
        
        return $pending;
    }
    
    
    public function migrate($output)
    {
        $migrations = $this->pendingMigrations();
        $runner = $this->builder->runner($this->adapter, $output);
        
        $last = null;
        
        foreach($migrations as $name => $file) {
            $runner->run($file);
            $last = $name;
        }
        
        if($last !== null) {
            $this->adapter->setLastMigration($last);
        }
        
        return array_keys($migrations);
    }
}