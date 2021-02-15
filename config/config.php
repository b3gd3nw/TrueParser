<?php

return [

    'database' => [
        'scheme' => getenv('DB_SCHEME'),
        'host' =>  getenv('DB_HOST'),
        'port' =>  getenv('DB_PORT')
    ]

];