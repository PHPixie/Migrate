<?php

namespace PHPixie\Migrate\Migrator\Output;

use PHPixie\Migrate\Migrator\Output;

class CLI implements Output
{
    protected $cliContext;
    
    public function __construct($cliContext)
    {
        $this->cliContext = $cliContext;
    }
    
    public function sql($sql)
    {
        $this->cliContext->writeLine($sql);
        $this->cliContext->writeLine('----');
    }
    
    public function message($string)
    {
        $this->cliContext->writeLine($sql);
        $this->cliContext->writeLine('----');
    }
}