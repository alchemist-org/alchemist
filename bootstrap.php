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

define('LOG_DIR', __DIR__.'/log');
define('TEMP_DIR', __DIR__.'/temp');
define('CONFIG', __DIR__.'/src/Config/config.neon');

// container
$containerLoader = new ContainerLoader(TEMP_DIR);
$class = $containerLoader->load(function($compiler) {
  /** @var \Nette\DI\Compiler $compiler */
  $compiler->loadConfig(CONFIG);
  return $compiler->compile();
},
  '');

$container = new $class;

return $container;
