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
use Alchemist\Template;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class TouchProjectCommandTest extends CommandTestCase
{

    /*public function testTouchNoExistProject()
    {
        $this->runCommand(
            $this->container->getByType(TouchProjectCommand::class),
            array(
                'name' => 'fooo'
            )
        );
    }*/

    public function testTouchProject()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME,
                '--save' => true
            )
        );

        $result = $this->runCommand(
            $this->container->getByType(TouchProjectCommand::class),
            array(
                'name' => $projectName
            )
        );
        Assert::truthy($result);
    }

}

$testCase = new TouchProjectCommandTest($container);
$testCase->run();
