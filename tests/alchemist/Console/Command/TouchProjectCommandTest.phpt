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

use Alchemist\Console\Command\CreateProjectCommand;
use Alchemist\Console\Command\TouchProjectCommand;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class TouchProjectCommandTest extends CommandTestCase  {

    public function testTouchNoExistProjectCommand() {
        $this->runCommand(
            $this->container->getByType(TouchProjectCommand::class),
            array('name' => 'fooo')
        );
    }

    public function testTouchProjectCommand() {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array('name' => $projectName)
        );

        $this->runCommand(
            $this->container->getByType(TouchProjectCommand::class),
            array('name' => $projectName)
        );
    }

}

$testCase = new TouchProjectCommandTest ($container);
$testCase->run();
