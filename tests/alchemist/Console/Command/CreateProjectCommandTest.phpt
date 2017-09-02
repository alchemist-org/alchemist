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
use Alchemist\Template;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class CreateProjectCommandTest extends CommandTestCase
{

    public function testCreateProject()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        Assert::truthy(file_exists($projectDir));
    }

    public function testCreateAndRemoveProjectWithNginxTemplate()
    {
        $projectName = 'fooooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => 'nginx'
            ]
        );

        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(file_exists($projectDir));
        Assert::falsey(empty($defaultDistanceSources));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false
            ]
        );

        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey(empty($defaultDistanceSources));
    }

    public function testCreateAndRemoveProjectWithApacheTemplate()
    {
        $projectName = 'fooooooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => 'apache'
            ]
        );

        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey(empty($defaultDistanceSources));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false
            ]
        );

        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey(empty($defaultDistanceSources));
    }

    public function testCreateProjectWithSave()
    {
        $projectName = 'baaar';

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSourceBefore));

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => Template::DEFAULT_TEMPLATE,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey(empty($defaultDistanceSource));
    }

    public function testCreateProjectDuplicateAndCatchException()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        Assert::error(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(CreateProjectCommand::class),
                [
                    'name' => $projectName,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        },
            '\Exception');
    }

    public function testCreateProjectNoProjectsDirCatchException()
    {
        $projectName = 'fooo';

        Assert::error(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(CreateProjectCommand::class),
                [
                    'name' => $projectName,
                    'template' => null
                ]
            );
        },
            '\Exception');
    }

    public function testCreateProjectWithForce()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        Assert::noError(function() use ($projectName) {
            $this->runCommand(
                $this->getCommand(CreateProjectCommand::class),
                [
                    'name' => $projectName,
                    '--force' => true,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                ]
            );
        });
    }

}

(new CreateProjectCommandTest($container))->run();
