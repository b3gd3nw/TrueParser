<?php

namespace App\Core;

abstract class Connect
{
    protected $connect;

    public function __construct()
    {
        $this->connect = Database::connect();
    }
}