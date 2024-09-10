<?php
use \evo\debug\Debug;

/*
 * http://localhost/evo/Debug/test/example.php
 */
require __DIR__.'/../vendor/autoload.php';

Debug::I()->regesterFunctions();

echo 'hello';