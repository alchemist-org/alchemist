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
use Alchemist\Console\Command\InstallCommand;
use Alchemist\Console\Command\RemoveProjectCommand;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class InstallProjectCommandTest extends CommandTestCase
{

    public function testInstallProjectsAndInstallProjectsWithForce()
    {
        $projectName = 'installProject';

        // create
        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => 'apache',
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::truthy(file_exists($projectDir));
        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSource));

        // remove
        Assert::noError(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(RemoveProjectCommand::class),
                [
                    'name' => $projectName,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        });

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::falsey(file_exists($projectDir));

        // install
        Assert::noError(function() {
            $this->runCommand(
                $this->getCommand(InstallCommand::class)
            );
        });

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $projectExists = file_exists($projectDir);
        Assert::truthy($projectExists);

        // install with --force
        Assert::noError(function() use ($projectName) {
            $result = $this->runCommand(
                $this->getCommand(InstallCommand::class),
                [
                    '--force' => true
                ]
            );
            Assert::truthy($result);
        });
        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $projectExists = file_exists($projectDir);
        Assert::truthy($projectExists);
    }

    public function testInstallProjectsAndTestInstallProjectsWithSuppress()
    {
        $projectName = 'installProject1';
        $projectName2 = 'installProject2';

        // create
        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => 'apache',
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );
        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName2,
                '--save' => true,
                '--template' => 'nginx',
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::truthy(file_exists($projectDir));
        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSource));

        // remove
        Assert::noError(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(RemoveProjectCommand::class),
                [
                    'name' => $projectName,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        });
        Assert::noError(function() use ($projectName2) {
            $this->runCommand(
                $this->getCommand(RemoveProjectCommand::class),
                [
                    'name' => $projectName2,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        });

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::falsey(file_exists($projectDir));

        // install
        Assert::noError(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(InstallCommand::class)
            );
        });

        // install with --suppress
        Assert::noError(function() {
            $result = $this->runCommand(
                $this->getCommand(InstallCommand::class),
                [
                    '--suppress' => true
                ]
            );
            Assert::truthy($result);
        });

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $projectExist = file_exists($projectDir);
        Assert::truthy($projectExist);
    }

}

$testCase = new InstallProjectCommandTest($container);
$testCase->run();
