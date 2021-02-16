<?php

namespace App\Core;

use Predis;

class Database
{

    private static $predis;
    private $connected = false;

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

    public function set($target, $data)
    {
        if (! $this->connected) {
            $this->connect();
        }
        var_dump($data);

        $this->predis->lpush($target, $data);
    }

    public function get($target)
    {
//        return $this->predis->get('0');
       return $this->predis->rpop($target);
    }


}
