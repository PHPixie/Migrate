<?php

namespace PHPixie\Migrate\Output;

use PHPixie\Migrate\Output;

class CLI implements Output
{
    protected $cliContext;
    
    public function __construct($cliContext)
    {
        $this->cliContext = $cliContext;
    }
    
    public function sql($sql)
    {
        $stream = $this->cliContext->outputStream();
        $stream->writeLine($sql);
        $stream->writeLine('----');
    }
    
    public function message($string)
    {
        $stream = $this->cliContext->outputStream();
        $stream->writeLine($string);
        $stream->writeLine('----');
    }
}