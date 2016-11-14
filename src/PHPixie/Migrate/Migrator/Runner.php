<?php

namespace PHPixie\Migrate\Migrator;

use PHPixie\Console\Exception\CommandException;

class Runner
{
    protected $adapter;
    protected $output;
    
    public function __construct($adapter, $output)
    {
        $this->adapter = $adapter;
        $this->output = $output;
    }
    
    public function run($file)
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        if(!in_array($extension, array('php', 'sql'))) {
            throw new CommandException("Migration $fileName is neither a .php nor a .sql file");
        }
        
        $this->output->message("Running $fileName");
        $method = 'run'.ucfirst($extension).'Migration';
        $this->$method($file);
    }
    
    public function runPhpMigration($file)
    {
        require $file;
    }
    
    public function runSqlMigration($file)
    {
        $sql = file_get_contents($file);
        $statements = preg_split("#^-- statement#m", $sql);
        foreach($statements as $sql) {
            $sql = trim($sql);
            $sql = rtrim($sql, ';');
            $this->execute($sql);
        }
    }
    
    public function execute($sql)
    {
        $this->output->sql($sql);
        return $this->adapter->execute($sql);
    }
    
    public function write($string)
    {
        $this->output->message($string);
    }
    
    public function connection()
    {
        return $this->adapter->connection();
    }
}