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
use Alchemist\Console\Command\RemoveProjectCommand;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class RemoveProjectCommandTest extends CommandTestCase
{

    public function testRemoveNoExistProjectCommand()
    {
        $projectName = 'foott';

        Assert::error(function () use ($projectName) {
            $this->runCommand(
                $this->container->getByType(RemoveProjectCommand::class),
                array('name' => $projectName)
            );
        }, '\Exception');
    }

    public function testRemoveProjectCommand()
    {
        $projectName = 'foooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array('name' => $projectName)
        );
        $this->runCommand(
            $this->container->getByType(RemoveProjectCommand::class),
            array('name' => $projectName)
        );
    }

}

$testCase = new RemoveProjectCommandTest($container);
$testCase->run();
