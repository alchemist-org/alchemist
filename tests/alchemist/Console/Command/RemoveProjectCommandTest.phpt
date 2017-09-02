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
use Alchemist\DistantSource;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class RemoveProjectCommandTest extends CommandTestCase
{

    public function testRemoveNoExistProject()
    {
        $projectName = 'foott';

        Assert::error(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(RemoveProjectCommand::class),
                [
                    'name' => $projectName,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        },
            '\Exception');
    }

    public function testRemoveProjectCommand()
    {
        $projectName = 'foooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::truthy(file_exists($projectDir));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $fileExist = file_exists($projectDir);
        Assert::falsey($fileExist);
    }

    public function testRemoveSavedProject()
    {
        $projectName = 'baaaaar';

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSourceBefore));

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSourceBefore));

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--projects-dir' => self::PROJECTS_DIR_NAME,
                '--force' => true
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey(empty($defaultDistanceSource));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSourceBefore));
    }

}

(new RemoveProjectCommandTest($container))->run();