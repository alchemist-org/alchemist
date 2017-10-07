#!/usr/bin/env php
<?php

use Symfony\Component\Console\Application;

$container = require __DIR__ . '/../bootstrap.php';

/** @var Application $application */
$application = $container->getByType(Application::class);
$application->run();