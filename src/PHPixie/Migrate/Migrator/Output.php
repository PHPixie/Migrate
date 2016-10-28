<?php

namespace PHPixie\Migrate\Migrator;

interface Output
{
    public function sql($sql);
    public function message($string);
}