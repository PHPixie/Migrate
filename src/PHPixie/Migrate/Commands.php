<?php

namespace PHPixie\Migrate;

class Commands extends \PHPixie\Console\Registry\Provider\Implementation
{
    protected $builder;
    
    public function __construct($builder)
    {
        $this->builder = $builder;
    }
    
    public function commandNames()
    {
        return array('migrate', 'database', 'seed');
    }
    
    public function buildMigrateCommand($config)
    {
        return new Commands\Migrate($this->builder, $config);
    }
    
    public function buildSeedCommand($config)
    {
        return new Commands\Seed($this->builder, $config);
    }
    
    public function buildDatabaseCommand($config)
    {
        return new Commands\Database($this->builder, $config);
    }
}