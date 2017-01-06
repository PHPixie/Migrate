<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;
use PHPixie\Slice\Data;

class Sqlite extends Adapter
{
    protected $quoteCharacter = '"';
    
    public function dropDatabase()
    {
        $this->connection->disconnect();
        
        $config = $this->connection->config();
        $file = $config->getRequired('file');
        
        if(file_exists($file)) {
            file_put_contents($file, '');
        }
    }

    public function createDatabase()
    {
        
    }
    
    public function truncateTable($table)
    {
        $this->execute('DELETE FROM '.$this->quote($table));
    }


    public function disableForeignKeyCheck()
    {
        $this->execute("PRAGMA foreign_keys = OFF");
    }

    public function enableForeignKeyCheck()
    {
        $this->execute("PRAGMA foreign_keys = ON");
    }
}