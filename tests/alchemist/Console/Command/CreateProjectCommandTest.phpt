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
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            )
        );
    }

    public function testCreateAndRemoveProjectWithNginxTemplate()
    {
        $projectName = 'fooooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--save' => true,
                '--template' => 'nginx'
            )
        );

        $hostName = $projectName.'.'.self::TLD;
        $rootDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . self::ROOT;
        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSources));
        Assert::truthy(file_exists(self::HOSTS_FILE));
        Assert::truthy($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::truthy(file_exists(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, self::PORT));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, $hostName));
        Assert::truthy($this->isStringInFile(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, $rootDir));

        $this->runCommand(
            $this->container->getByType(RemoveProjectCommand::class),
            array(
                'name' => $projectName,
                '--save' => false
            )
        );

        Assert::falsey($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::falsey(file_exists(self::NGINX_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
    }

    public function testCreateAndRemoveProjectWithApacheTemplate()
    {
        $projectName = 'fooooooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--save' => true,
                '--template' => 'apache'
            )
        );

        $hostName = $projectName.'.'.self::TLD;
        $rootDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . self::ROOT;
        $defaultDistanceSources = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSources));
        Assert::truthy(file_exists(self::HOSTS_FILE));
        Assert::truthy($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::truthy(file_exists(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, self::PORT));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, $hostName));
        Assert::truthy($this->isStringInFile(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName, $rootDir));

        $this->runCommand(
            $this->container->getByType(RemoveProjectCommand::class),
            array(
                'name' => $projectName,
                '--save' => false
            )
        );

        Assert::falsey($this->isStringInFile(self::HOSTS_FILE, $hostName));
        Assert::falsey(file_exists(self::APACHE_SITES_ENABLED . DIRECTORY_SEPARATOR . $projectName));
    }

    public function testCreateProjectWithSave()
    {
        $projectName = 'baaar';

        $defaultDistanceSourceBefore = $this->configurator->getConfig()->getDistantSource('default');
        Assert::truthy(empty($defaultDistanceSourceBefore));

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--save' => true,
                '--template' => 'default'
            )
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource('default');
        Assert::falsey(empty($defaultDistanceSource));
    }

    public function testCreateProjectDuplicateAndCatchException()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME
            )
        );

        Assert::error(function () use ($projectName) {
            $this->runCommand(
                $this->container->getByType(CreateProjectCommand::class),
                array(
                    'name' => $projectName,
                    '--projects-dir' => self::PROJECTS_DIR_NAME
                )
            );
        }, '\Exception');
    }

    public function testCreateProjectNoProjectsDirCatchException()
    {
        $projectName = 'fooo';

        Assert::error(function () use ($projectName) {
            $this->runCommand(
                $this->container->getByType(CreateProjectCommand::class),
                array(
                    'name' => $projectName,
                    'template' => null
                )
            );
        }, '\Exception');
    }

    public function testCreateProjectWithForce()
    {
        $projectName = 'fooo';

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--projects-dir' => self::PROJECTS_DIR_NAME)
        );

        $this->runCommand(
            $this->container->getByType(CreateProjectCommand::class),
            array(
                'name' => $projectName,
                '--force' => true
            )
        );
    }

}

$testCase = new CreateProjectCommandTest($container);
$testCase->run();
