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
define('TEST_PROJECTS_DIR2', TEMP__DIR__ . '/projects-dir2');

// clear test directories
\Tester\Helpers::purge(TEST_PROJECTS_DIR);
\Tester\Helpers::purge(TEST_PROJECTS_DIR2);
\Tester\Helpers::purge(TEST_TEMP_DIR);

$loader->setTempDirectory(TEST_TEMP_DIR);
$loader->register(true);

return $container;