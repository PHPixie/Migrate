<?php

namespace PHPixie\Migrate\Commands;

use PHPixie\Console\Command\Config;
use \PHPixie\Console\Command\Implementation as Command;
use PHPixie\Database\Connection;
use PHPixie\Migrate\Builder;
use PHPixie\Slice\Data;

class Migrate extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    public function __construct(Config $config)
    {
        $config->argument('config')
            ->description("Migration configuration name, defaults to 'default'");

        parent::__construct($config);
    }

    public function run(Data $optionData, Data $argumentData)
    {
        $configName = $argumentData->get('config', 'default');
        $config = $this->builder->getConfig($configName);

        $databaseConfig = $this->builder->database()->connectionConfig(
            $config->
        );

        $adapter = $this->builder->getAdapter()
        $namespace = $config->get('namespace', '');

        $iterator = new \DirectoryIterator(
            $config->getRequired('path'),
            \FilesystemIterator::SKIP_DOTS
        );

        foreach($iterator as $file) {
            $class = $file->getBasename('.php');
            $class = $namespace."\\".$class;
            require_once $file->getRealPath();
            $migration = new $class(
                $this->cliContext(),
                $configName,
                $config
            );
            $migration->run();
        }
    }
}