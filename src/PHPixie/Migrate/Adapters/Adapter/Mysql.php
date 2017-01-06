<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Mysql extends Adapter
{
    protected $quoteCharacter = '`';

    public function disableForeignKeyCheck()
    {
        $this->execute("SET FOREIGN_KEY_CHECKS=0");
    }

    public function enableForeignKeyCheck()
    {
        $this->execute("SET FOREIGN_KEY_CHECKS=1");
    }
}