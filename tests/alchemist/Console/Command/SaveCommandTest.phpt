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
use Alchemist\Console\Command\SaveCommand;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class SaveCommandTest extends CommandTestCase
{

    public function testSave()
    {
        $projectName = 'fooo';

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::truthy(empty($defaultDistanceSource));

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::truthy(empty($defaultDistanceSource));

        $this->runCommand(
            $this->getCommand(SaveCommand::class)
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::equal(1, count($defaultDistanceSource));
    }

}

$testCase = new SaveCommandTest($container);
$testCase->run();
