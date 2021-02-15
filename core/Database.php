<?php

namespace App\Core;

use Predis;

class Database
{

    private static $predis;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->predis = new Predis\Client($config['database']);

        return $this->predis;
    }

    public function connect()
    {
        if (self::$predis === null) {
            self::$predis = new self();
        }
        return self::$predis;
    }

    public function set()
    {
//        $this->predis->set(';message', ';Hello world');
        $url_conf = require __DIR__ . '/../config/url_conf.php';
        $this->predis->set("tasks", json_encode($url_conf));
    }

    public function get()
    {
//        return $this->predis->get('0');
       return $this->predis->get("tasks");
    }


}
