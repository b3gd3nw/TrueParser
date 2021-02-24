<?php

namespace App\Core;

abstract class Connect
{
    protected $connect;
    protected $mysql_connect;

    public function __construct()
    {
        $this->connect = new Database;
        $this->mysql_connect = new DatabaseMySQL;
    }
}