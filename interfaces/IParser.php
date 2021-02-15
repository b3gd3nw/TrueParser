<?php

namespace App\Interfaces;

interface IParser {
    public function parse(string $url);
    public function getPage(string $url);
}