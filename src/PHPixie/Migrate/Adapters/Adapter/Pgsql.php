<?php

namespace PHPixie\Migrate\Adapters\Adapter;

use PHPixie\Migrate\Adapters\Adapter;

class Pgsql extends Adapter
{
    protected $quoteCharacter = '"';
}