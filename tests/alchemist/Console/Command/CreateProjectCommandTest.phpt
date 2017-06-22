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

        $hostName = $projectName . '.' . self::TLD;
        $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;
        $rootDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . self::ROOT;
        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::truthy(file_exists($projectDir));
        Assert::falsey(empty($defaultDistanceSources));
        Assert::truthy(file_exists(self::HOSTS_FILE));
        Assert::truthy($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::truthy(file_exists(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName,
            self::PORT));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName,
            $hostName));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, $rootDir));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false
            ]
        );

        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSources));
        Assert::falsey($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::falsey(file_exists(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
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

        $hostName = $projectName . '.' . self::TLD;
        $rootDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . self::ROOT;
        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSources));
        Assert::truthy(file_exists(self::HOSTS_FILE));
        Assert::truthy($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::truthy(file_exists(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName . '.conf'));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName . '.conf',
            self::PORT));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName . '.conf',
            $hostName));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName . '.conf',
            $rootDir));

        $this->runCommand(
            $this->getCommand(RemoveProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => false
            ]
        );

        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSources));
        Assert::falsey($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::falsey(file_exists(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
    }

    public function testCreateProjectWithSave()
    {
        $projectName = 'baaar';

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource('default');
        Assert::truthy(empty($defaultDistanceSourceBefore));

        $this->runCommand(
            $this->getCommand(CreateProjectCommand::class),
            [
                'name' => $projectName,
                '--save' => true,
                '--template' => 'default',
                '--projects-dir' => self::PROJECTS_DIR_NAME
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
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
                    '--force' => true
                ]
            );
        });
    }

}

$testCase = new CreateProjectCommandTest($container);
$testCase->run();
