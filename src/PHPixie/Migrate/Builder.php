<?php

namespace PHPixie\Migrate;

use PHPixie\Migrate\Adapters\Adapter;

class Builder
{
    protected $database;
    protected $root;
    protected $config;
    
    protected $commands;
    protected $files;
    
    public function __construct($database, $root, $config)
    {
        $this->database = $database;
        $this->root = $root;
        $this->config = $config;
    }
    
    public function commands()
    {
        if($this->commands === null) {
            return new Commands($this);
        }
        
        return $this->commands;
    }

    public function files()
    {
        if($this->files === null) {
            return new Files($this->root);
        }
        
        return $this->files;
    }

    public function migrator($name = 'default')
    {
        $config = $this->config->slice('migrations.'.$name);
        $connectionName = $config->getRequired('connection');
        $adapter = $this->adapter($connectionName);
        
        return $this->buildMigrator($adapter, $config);
    }
    
    
    public function runner($adapter, $output)
    {
        return new Migrator\Runner($adapter, $output);
    }
    
    public function seeder($name = 'default')
    {
        $config = $this->config->slice('seeds.'.$name);
        $connectionName = $config->getRequired('connection');
        $adapter = $this->adapter($connectionName);
        
        return $this->buildSeeder($adapter, $config);
    }
    
    public function adapter($databaseName)
    {
        $connection = $this->database->get($databaseName);
        if(!($connection instanceof \PHPixie\Database\Driver\PDO\Connection)) {
            throw new \PHPixie\Migrate\Exception("Migrate only supports PDO database connections");
        }
        
        $type = $connection->adapterName();
        return $this->buildAdapter($type, $connection);
    }
    
    public function cliOutput($cliContext)
    {
        return new Output\CLI($cliContext);
    }
    
    /**
     * @param $name
     * @return Adapter
     */
    public function buildAdapter($type, $connection)
    {
        $class = 'PHPixie\Migrate\Adapters\Adapter\\'.ucfirst($type);
        return new $class($connection);
    }
    
    public function buildMigrator($adapter, $config)
    {
        return new Migrator($this, $adapter, $config);
    }
    
    public function buildSeeder($adapter, $config)
    {
        return new Seeder($this, $adapter, $config);
    }
}