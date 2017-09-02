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
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class TouchProjectCommandTest extends CommandTestCase
{

    public function testTouchNoExistProject()
    {
        $result = $this->runCommand(
            $this->getCommand(TouchProjectCommand::class),
            [
                'name' => 'fooo'
            ]
        );

        Assert::falsey($result);
    }

    public function testTouchProject()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME,
                '--save' => true
            ]
        );

        $result = $this->runCommand(
            $this->getCommand(TouchProjectCommand::class),
            [
                'name' => $projectName
            ]
        );

        Assert::truthy($result);
    }

}

(new TouchProjectCommandTest($container))->run();

