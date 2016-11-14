<?php

namespace PHPixie\Migrate\Commands;

use PHPixie\Console\Command\Config;
use PHPixie\Console\Command\Implementation as Command;
use PHPixie\Database\Connection;
use PHPixie\Migrate\Builder;
use PHPixie\Slice\Data;
use PHPixie\Migrate\Exception;
use PHPixie\Console\Exception\CommandException;

class Database extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder, Config $config)
    {
        $this->builder = $builder;
        
        $config->description("Create or drop a database");
        
        $config->argument('action')
            ->required()
            ->description("Either 'create' or 'drop'");
        
        $config->argument('config')
            ->description("Migration configuration name, defaults to 'default'");
        
        parent::__construct($config);
    }

    public function run($argumentData, $optionData)
    {
        $configName = $argumentData->get('config', 'default');
        $migrator = $this->builder->migrator($configName);
        
        $action = $argumentData->getRequired('action');
        
        if(!in_array($action, array('create', 'drop'))) {
            throw new CommandException("ACTION must be either 'create' or 'drop'");
        }
        
        $this->$action($migrator, $optionData);
    }
    
    protected function create($migrator, $optionData)
    {
        $migrator->createDatabase();
        $this->writeLine("Database succesfully created or already exists");
    }
    
    protected function drop($migrator, $optionData)
    {
        $migrator->dropDatabase();
        $this->writeLine("Database succesfully dropped or does not exist");
    }
}