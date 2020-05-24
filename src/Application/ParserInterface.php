<?php

namespace App\Application;

interface ParserInterface
{
    public function parse($cases): string;
}
