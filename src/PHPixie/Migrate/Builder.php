<?php

namespace PHPixie\Migrate;

use PHPixie\Migrate\Adapters\Adapter;

class Builder
{
    protected $adapters = [];

    /**
     * @param $name
     * @return Adapter
     */
    public function adapter($name)
    {
        if(!isset($this->adapters[$name])) {
            $class = 'PHPixie\Migrate\Adapters\Adapter\\'.ucfirst($name);
            $this->adapters[$name] = new $class;
        }


        return $this->adapters[$name];
    }
}