<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Mysql extends Adapter
{
    protected $quoteCharacter = '`';
}