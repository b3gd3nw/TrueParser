<?php

namespace App\Core;

class Main extends Connect
{
    public $_pids= [];

    public function start()
    {

        if(!$this->connect->get('tasks'))
        {
            $url_conf = require __DIR__ . '/../config/url_conf.php';
            $this->connect->set('tasks', json_encode($url_conf));
        }

//        while(true)
//        {
            if(count($this->_pids) < 5)
            {
                var_dump(3122);
                $task = $this->connect->get('tasks');
                var_dump($task);
                $task = get_object_vars(json_decode($task));
                $class = "App\\Parsers\\{$task['class']}";
                $class = new $class;
                $url = $task['url'];


                $pid = pcntl_fork();
                if ($pid == -1){
                    die('Error forking');
                }
                if ($pid) {
                    var_dump('Parent:', $pid);
                }else{
                    $class->parse($url);
                    $this->_pids = $pid;
                    var_dump($this->_pids);
                    exit();
                }
            } else {
                sleep(10);
            }
//        }

    }
}