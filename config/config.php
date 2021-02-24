<?php

return [

    'database' => [
        'scheme' => $_SERVER['RDB_SCHEME'],
        'host' => $_SERVER['RDB_HOST'],
        'port' => $_SERVER['RDB_PORT']
    ],

    'databasemysql' => [
        'name' => $_SERVER['MSQLDB_NAME'], //database name
        'username' => $_SERVER['MSQLDB_USERNAME'], //database username
        'password' => $_SERVER['MSQLDB_PASSWORD'], //database password
        'connection' => $_SERVER['MSQLDB_CONNECTION'], //database host
        'options' => [
            PDO::ATTR_TIMEOUT => 100,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION

        ]
    ]

];