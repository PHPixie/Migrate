<?php

namespace PHPixie\Migrate\Commands;

use PHPixie\Console\Command\Config;
use PHPixie\Console\Command\Implementation as Command;
use PHPixie\Database\Connection;
use PHPixie\Migrate\Builder;
use PHPixie\Slice\Data;
use PHPixie\Migrate\Exception;

class Database extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder, Config $config)
    {
        $this->builder = $builder;
        
        $config->argument('action')
            ->required()
            ->description("Either 'create' or 'drop'");
        
        $config->argument('config')
            ->description("Migration configuration name, defaults to 'default'");
        
        $config->option('--drop')
            ->flag()
            ->description("A safety flag aims to prevent accidental dropping of the database");

        parent::__construct($config);
    }

    public function run(Data $optionData, Data $argumentData)
    {
        $configName = $argumentData->get('config', 'default');
        $migrator = $this->builder->migrator($configName);
        
        $action = $argumentData->getRequired('action');
        
        if(!in_array($action, array('create', 'drop'))) {
            throw new CommandException("ACTION must be either 'create' or 'drop'");
        }
        
        $this->$action($migrator, $optionData);
    }
    
    protected function create($migrator, Data $optionData)
    {
        $migrator->createDatabase();
        $this->writeLine("Database succesfully created or already exists");
    }
    
    protected function drop($migrator, Data $optionData)
    {
        if(!$optionData->get('drop')) {
            throw new CommandException("If you really want to drop the database rerun this command with --drop");
        }
        
        $migrator->dropDatabase();
        $this->writeLine("Database succesfully dropped or does not exist");
    }
}