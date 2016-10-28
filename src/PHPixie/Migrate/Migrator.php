<?php

namespace PHPixie\Migrate;

class Migrator
{
    protected $builder;
    protected $config;
    protected $allowedExtensions = array('php', 'sql');
    
    public function __construct($config)
    {
        $this->config = $config;
    }
    
    public function adapter()
    {
        if($this->adapter === null) {
            $this->config->getRequired('type');
            $this->adapter = $this->builder->adapter($type, $this->config);
        }
        
        return $this->adapter;
    }
    
    public function lastMigration()
    {
        return $this->adapter()->getLastMigration();
    }
    
    public function pendingMigrations()
    {
        $path = $config->getRequired('path').'/';
        
        if(!file_exists($path) || !is_dir($path)) {
            throw new Exception("Path $path is not a directory");
        }
        
        $files = array();
        foreach(scandir($path as $file)) {
            if($file{0} == '.') {
                continue;
            }
            
            $baseName = strtolower(pathinfo($file, PATHINFO_BASENAME));
            if(isset($files[$basename])) {
                throw new Exception("Multiple migrations with name $basename present");
            }
            
            $filePath = $path.$file;
            if(!is_file($fullPath)) {
                throw new Exception("Path $filePath is not a regular file");
            }
            
            $files[$baseName] = $file;
        }
        
        ksort($files, SORT_STRING);
        
        $pending = array();
        
        foreach($files as $name => $file) {
            if(strcmp($name, strtolower($lastMigration)) <= 0) {
                break;
            }
            
            $pending[pathinfo($file, PATHINFO_BASENAME)] = $path.$file;
        }
        
        return $pending;
    }
    
    
    public function migrate($output)
    {
        $migrations = $this->pendingMigrations();
        $runner = $this->builder->runner($this->adapter(), $output);
        
        foreach($migrations as $name => $file) {
            $output->message("Running $name");
            $runner->run($file);
        }
        
        return array_keys($pendingMigrations);
    }
    
    public function runSql($output, $file)
    {
        $sql = file_get_contents($file);
        $statements = preg_split("#^----[ \t]*$#", $sql);
        $adapter = $this->adapter();
        
        foreach($statements as $sql) {
            $sql = trim($sql);
            $trimmed = rtrim($sql, ';');
            $output->sql($sql);
            $adapter->execute($trimmed);
        }
    }
    
    public function runPhp($output, $file)
    {
        $runner = $this->builder->runner($this->adapter, $output);
        $runner->run($p)
    }
}