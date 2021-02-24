<?php
namespace App\Core;

use PDO;

class DatabaseMySQL
{
    private $pdo;

    public function __construct(){
        try {
            $config = require __DIR__ . '/../config/config.php';
            $config = $config['databasemysql'];
            $this->pdo = new PDO(
                $config['connection'].';dbname='.$config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );


        } catch (\Exception $exception) {
            echo "\nUnseccess connect to the MySQL -> {$exception}";
        }

        return $this->pdo;
    }

    /**
     * Connect to database
     *
     * @return PDO
     */
    public function connect()
    {
        if ($this->pdo === null) {
           $this->__construct();
        }
        return $this->pdo;
    }

    /**
     * Insert data into database
     *
     * @param  string  $table
     * @param  string|array  $data
     * @param  string  $type
     * @return string
     */
    public function insert($table, $data, $type)
    {
        $this->pdo = null;
        $this->connect();

        if ($table == 'Answers'){
            $sql = "INSERT INTO {$table} ({$type}, length) VALUES (:data, :length)";
            $query = $this->pdo->prepare($sql);
            $query->execute([
                ':data' => $data['answer'],
                ':length' => $data['length']
            ]);
        } else if ($table == 'Questions'){
            $sql = "INSERT INTO {$table} ({$type}) VALUES (:data)";
            $query = $this->pdo->prepare($sql);
            $query->execute([
                ':data' => $data
            ]);
        } else {
            $sql = sprintf("INSERT INTO {$table} (question_id, answer_id) VALUES (:question_id, :answer_id)");
            $query = $this->pdo->prepare($sql);
            $query->execute([
                ':question_id' => $data['question_id'],
                ':answer_id' => $data['answer_id']
            ]);
        }
        return $this->pdo->lastInsertId();
    }

    /**
     * Check existence in the database
     *
     * @param  string  $table
     * @param  string|array $data
     * @param  string  $type
     * @return false|int
     */
    public function check($table, $data, $type)
    {
        $this->pdo = null;
        $this->connect();

        $sql = "SELECT id FROM {$table} where {$type}=:data";

        $query = $this->pdo->prepare($sql);
        $query->execute([ ':data' => $data ]);
        $id = $query->fetch();
        if ($id == false){
            return false;
        } else {
            return intval($id['id']);
        }
    }

}

