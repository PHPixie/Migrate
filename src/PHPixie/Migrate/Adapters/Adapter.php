<?php

namespace PHPixie\Migrate\Adapters;

use PHPixie\Slice\Data;
use PDO;

abstract class Adapter
{
    abstract public function dropDatabase(Data $config);
    abstract public function createDatabase(Data $config);

    protected function buildPdo($dsn, Data $config)
    {
        $pdo = new PDO(
            $dsn,
            $config->get('user',''),
            $config->get('password', ''),
            $config->get('connectionOptions', array())
        );

        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        return $pdo;
    }
}