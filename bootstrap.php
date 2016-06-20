<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Nette\DI\ContainerLoader;

require __DIR__.'/vendor/autoload.php';

$logDir = __DIR__.'/log';
$tempDir = __DIR__.'/temp';
$config = __DIR__.'/src/Config/config.neon';

// container
$containerLoader = new ContainerLoader($tempDir);
$class = $containerLoader->load(function($compiler) use ($config) {
  /** @var \Nette\DI\Compiler $compiler */
  $compiler->loadConfig($config);
  return $compiler->compile();
},
  '');

$container = new $class;

return $container;
