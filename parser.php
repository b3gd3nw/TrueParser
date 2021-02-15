<?php

require 'vendor/autoload.php';
require('env.php');

use App\Core\Main;

var_dump(getenv('DB_SCHEME'));

$test = new Main();
$test->start();