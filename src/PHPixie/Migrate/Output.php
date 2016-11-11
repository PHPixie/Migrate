<?php

namespace PHPixie\Migrate;

interface Output
{
    public function sql($sql);
    public function message($string);
}