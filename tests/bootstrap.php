<?php

$container = require_once __DIR__ . '/../bootstrap.php';

// create temporary directory
define('TEMP__DIR__', __DIR__.'/tmp/test'.getmypid());
@mkdir(dirname(TEMP__DIR__)); // @ - directory may already exist
\Tester\Helpers::purge(TEMP__DIR__);

return $container;