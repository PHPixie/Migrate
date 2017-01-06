<?php

namespace PHPixie\Migrate;

use PHPixie\Console\Exception\CommandException;

class Seeder
{
    protected $builder;
    protected $adapter;
    protected $config;
    
    public function __construct($builder, $adapter, $config)
    {
        $this->builder = $builder;
        $this->adapter = $adapter;
        $this->config = $config;
    }
    
    public function seed($output, $truncateTables)
    {
        $path = $this->config->getRequired('path');
        $files = $this->builder->files()->getFileMap($path);
        
        if(!$truncateTables) {
            foreach($files as $table => $file) {
                if(!$this->adapter->isTableEmpty($table)) {
                    throw new Exception("Table '$table' is not empty.");
                }
            }
        }

        $this->adapter->disableForeignKeyCheck();

        try {
            foreach ($files as $table => $file) {
                $data = $this->getData($file);
                $count = count($data);

                if ($truncateTables) {
                    $output->message("Truncating '$table'");
                    $this->adapter->truncateTable($table);
                }

                $output->message("Inserting $count item(s) into '$table'");
                $this->adapter->insertData($table, $data);
            }
        } finally {
            $this->adapter->disableForeignKeyCheck();
        }
    }
    
    public function getData($file)
    {
        $fileName = pathinfo($file, PATHINFO_FILENAME);
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        
        if(!in_array($extension, array('php', 'json'))) {
            throw new CommandException("Migration $fileName is neither a .php nor a .json file");
        }
        
        $method = 'get'.ucfirst($extension).'Data';
        return $this->$method($file);
    }

    public function getJsonData($file)
    {
        $data = file_get_contents($file);
        $data = json_decode($data, true);
        if(json_last_error() !== JSON_ERROR_NONE) {
            $error = json_last_error_msg();
            throw new Exception("Cannot parse JSON in '$file': $error");
        }
        
        return $data;
    }
    
    public function getPhpData($file)
    {
        $data = require $file;
        if(empty($data)) {
            $data = array();
        }
        
        return $data;
    }
    
    public function connection()
    {
        return $this->adapter->connection();
    }
}