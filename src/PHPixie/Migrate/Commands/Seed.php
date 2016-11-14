<?php

namespace PHPixie\Migrate\Commands;

use PHPixie\Console\Command\Config;
use PHPixie\Console\Command\Implementation as Command;
use PHPixie\Database\Connection;
use PHPixie\Migrate\Builder;
use PHPixie\Slice\Data;
use PHPixie\Migrate\Exception;
use PHPixie\Console\Exception\CommandException;

class Seed extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder, Config $config)
    {
        $this->builder = $builder;
        
        $config->description("Seed the database with data");
        $config->argument('config')
            ->description("Seed configuration name, defaults to 'default'");
        
        $config->option('truncate')
            ->flag()
            ->description("Truncate the tables before inserting the data.");

        parent::__construct($config);
    }

    public function run($argumentData, $optionData)
    {
        $configName = $argumentData->get('config', 'default');
        $seeder = $this->builder->seeder($configName);
        
        $output = $this->builder->cliOutput($this->cliContext());
        
        try{
            $seeder->seed($output, $optionData->get('truncate', false));
        } catch (\Exception $e) {
            throw new CommandException($e->getMessage());
        }
        
        $this->writeLine("Seed data successfully inserted.");
    }
}