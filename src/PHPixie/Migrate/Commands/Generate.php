<?php

namespace PHPixie\Migrate\Commands;

use PHPixie\Console\Command\Config;
use PHPixie\Console\Command\Implementation as Command;
use PHPixie\Database\Connection;
use PHPixie\Migrate\Builder;
use PHPixie\Slice\Data;
use PHPixie\Migrate\Exception;
use PHPixie\Console\Exception\CommandException;

class Generate extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Builder $builder, Config $config)
    {
        $this->builder = $builder;
        
        $config->description("Generate a new migration");
        $config->argument('config')
            ->description("Migration configuration name, defaults to 'default'");

        parent::__construct($config);
    }

    public function run($argumentData, $optionData)
    {
        $configName = $argumentData->get('config', 'default');
        $migrator = $this->builder->migrator($configName);
        
        $output = $this->builder->cliOutput($this->cliContext());
        
        try{
            $executed = $migrator->migrate($output);
        } catch (\Exception $e) {
            throw new CommandException($e->getMessage());
        }
        
        if(empty($executed)) {
            $this->writeLine("Already on latest version.");
            return;
        }
        
        $count = count($executed);
        $this->writeLine("Applied $count migration(s)");
    }
}