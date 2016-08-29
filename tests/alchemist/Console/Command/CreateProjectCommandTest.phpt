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
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class CreateProjectCommandTest extends CommandTestCase  {

    public function testCreateProjectCommand()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array('name' => $projectName)
        );
    }

    public function testCreateProjectWithSaveCommand() {
        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array('name' => 'baaar'),
            array('save' => true)
        );
    }

    public function testCreateProjectDuplicateAndCatchExceptionCommand()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array('name' => $projectName)
        );

        Assert::error(function () use ($projectName) {
            $this->runCommand(
                $this->container->getByType(CreateProjectCommand::class),
                array('name' => $projectName)
            );
        }, '\Exception');
    }

}

$testCase = new CreateProjectCommandTest($container);
$testCase->run();
