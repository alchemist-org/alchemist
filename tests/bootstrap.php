<?php

$container = require_once __DIR__ . '/../bootstrap.php';

// create temporary directory
define('TEMP__DIR__', __DIR__.'/temp/test'.getmypid());
@mkdir(dirname(TEMP__DIR__)); // @ - directory may already exist
\Tester\Helpers::purge(TEMP__DIR__);

// set autoloader
$loader = new \Nette\Loaders\RobotLoader();
$loader->addDirectory(__DIR__ . '/alchemist');

// test directories
define('TEST_TEMP_DIR', TEMP__DIR__ . '/temp');
define('TEST_PROJECTS_DIR', TEMP__DIR__ . '/projects-dir');

// clear test directories
if(!file_exists(TEST_PROJECTS_DIR)) {
    mkdir(TEST_PROJECTS_DIR);
} else {
    \Tester\Helpers::purge(TEST_PROJECTS_DIR);
}

if(!file_exists(TEST_TEMP_DIR)) {
    mkdir(TEST_TEMP_DIR);
} else {
    \Tester\Helpers::purge(TEST_TEMP_DIR);
}


$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(TEST_TEMP_DIR));
$loader->register(true);

return $container;