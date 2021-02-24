<?php

namespace App\Core;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Main extends Connect
{
    public $_pids= [];
    public $task;

    /**
     * Initialization parsing and forking
     */
    public function start()
    {
        /* Max count simultaneously live child processes */
        $max_child_processes = $_SERVER['MAX_CHILD_PROCESSES'];

        $stop_server = true;

        //Create Main class logger
        $info_log = new Logger('MAIN CLASS INFO LOG');

        $info_log->pushHandler(new StreamHandler(__DIR__ . '/../log/main.log', Logger::INFO));

        //Checking the task queue, if there are no tasks, we take the initial task
        $this->task = $this->connect->get('tasks');

        if($this->task == null)
        {
            $url_conf = require __DIR__ . '/../config/url_conf.php';
            $this->connect->set('tasks', json_encode($url_conf));
        }

        while($stop_server == true)
        {
            if((count($this->_pids) < $max_child_processes) and $this->connect->exists('tasks') == true)
            {
                $task = $this->getTask();

                $child_pid = pcntl_fork();

                if ($child_pid == -1){

                    die('Error forking' . PHP_EOL);

                } else if ($child_pid) {

                    $this->_pids[$child_pid] = true;
                    $info_log->info('In progress task: ', array(
                        'executor' => $task['class'],
                        'target' => $task['url'],
                        'Process ID' => $child_pid
                    ));

                } else {
                    echo 'NEW CHILD' . PHP_EOL;
                   $task['class']->parse($task['url']);
                   $info_log->info('Done task: ', array(
                       'url' => $task['url']
                   ));
                    exit('Child closed' . PHP_EOL);
                }
            } else {
                sleep(5);
            }

            // Checking processes for completion
            foreach (array_keys($this->_pids) as $pid)
            {
                $res = pcntl_waitpid($pid, $status, WNOHANG);
                // If the process has already exited
                if($res == -1 || $res > 0) {
                    unset($this->_pids[$pid]);
                    $info_log->info('PIDs Array: ', $this->_pids);
                }
            }

            // Completion check
            if (count($this->_pids) == 0 and $this->connect->exists('tasks') == false)
            {
                $stop_server = false;
                exit('The End' . PHP_EOL);
            }

        }
    }

    /**
     * Receives and transforms a task
     *
     * @return array
     */
    public function getTask()
    {
        $this->task = $this->connect->get('tasks');
        $task = json_decode($this->task, true);
        
        $class = "App\\Parsers\\{$task['class']}";
        $class = new $class;
        $url = $task['url'];


        return $to_do= [ 'class' => $class, 'url' => $url ];
    }


}