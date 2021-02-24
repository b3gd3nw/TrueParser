<?php

require 'vendor/autoload.php';
require 'env.php';

use App\Core\Main;

echo getenv('RDB_SCHEME');
$parser = new Main();
$parser->start();
