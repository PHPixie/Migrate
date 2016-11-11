<?php

namespace PHPixie;

class Migrate
{
    protected $builder;
    
    public function __construct($database, $root, $config)
    {
        $this->builder = $this->buildBuilder($database, $root, $config);
    }
    
    public function builder()
    {
        return $this->builder;
    }
    
    public function consoleCommands()
    {
        return $this->builder->commands();
    }
    
    public function migrator($name)
    {
        return $this->builder->migrator($name);
    }
    
    protected function buildBuilder($database, $root, $config)
    {
        return new Migrate\Builder($database, $root, $config);
    }
}