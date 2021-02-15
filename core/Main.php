<?php

namespace App\Core;

class Main extends Connect
{
    public $_pids;

    public function start()
    {
        if(!$this->connect->get())
        {
            $this->connect->set();
        }
        $task = $this->connect->get();

        $task = get_object_vars(json_decode($task)[0]);
        var_dump($task);
        $class = "App\\Parsers\\{$task['class']}";
        $class = new $class;
        $url = $task['url'];

//        foreach ($tasks as $task) {
        $pid = pcntl_fork();

        if ($pid) {
            var_dump('Parent:', $pid);
        }else{
            $class->parse($url);
            var_dump('PID:', $pid);
            exit();
        }
//        }
    }

}