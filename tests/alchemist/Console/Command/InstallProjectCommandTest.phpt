<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Console\Command;

use Alchemist\Console\Command\InstallCommand;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class InstallProjectCommandTest extends CommandTestCase  {

    public function testInstallProjects()
    {
        $this->runCommand(
            $this->container->getByType(InstallCommand::class)
        );
    }

}

$testCase = new InstallProjectCommandTest($container);
$testCase->run();
