<?php

require 'vendor/autoload.php';
require('env.php');

use App\Core\Main;

$test = new Main();
$test->start();