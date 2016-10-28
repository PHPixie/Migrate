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
        return array('run', 'database');
    }
    
    public buildRunCommand($config)
    {
        return new Commands\Run($this->builder, $config);
    }
    
    public buildDatabaseCommand($config)
    {
        return new Commands\Database($this->builder, $config);
    }
}