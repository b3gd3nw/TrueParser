<?php

namespace App\Core;

use Predis;

class Database
{

    private $predis;
    private $connected = false;

    public function __construct()
    {
        $config = require __DIR__ . '/../config/config.php';
        $this->predis = new Predis\Client($config['database']);

        return $this->predis;
    }

    /**
     * Connect to database
     *
     * @return Database
     */
    public function connect()
    {
        if ($this->predis === null) {
            $this->predis = new self();
        }
        return $this->predis;
    }

    /**
     * Add to queue
     *
     * @param  string  $target
     * @param  string|array  $data
     */
    public function set($target, $data)
    {
        if (! $this->connected) {
            $this->connect();
        }

        $this->predis->lpush($target, $data);
    }

    /**
     * Get out of queue
     *
     * @param  string  $target
     * @return string|null
     */
    public function get($target)
    {
       return $this->predis->rpop($target);
    }

    /**
     * Check existence in the queue
     *
     * @param  string  $target
     * @return int
     */
    public function exists($target)
    {
        return $this->predis->exists($target);
    }


}
