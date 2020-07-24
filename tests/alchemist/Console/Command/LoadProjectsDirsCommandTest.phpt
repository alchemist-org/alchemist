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

use Alchemist\Console\Command\LoadProjectsDirsCommand;
use Alchemist\DistantSource;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class LoadProjectsDirsCommandTest extends CommandTestCase
{

    public function testLoadProjectsDirsByName()
    {
        $projectsDirName = 'nginx';

        $this->createProject('foooo', false, $projectsDirName);

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::equal(0, count($defaultDistanceSource));

        $this->runCommand(
            $this->getCommand(LoadProjectsDirsCommand::class),
            [
                'name' => $projectsDirName
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::equal(1, count($defaultDistanceSource));
    }

    public function testLoadProjectsDirsByPath()
    {
        $projectsDirName = 'nginx';

        $this->createProject('foooo', false, $projectsDirName);

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal(3, count($projectsDirs));
        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::equal(0, count($defaultDistanceSource));

        $this->runCommand(
            $this->getCommand(LoadProjectsDirsCommand::class),
            [
                'path' => TEST_PROJECTS_DIR
            ]
        );

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal(3, count($projectsDirs));
        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::equal(1, count($defaultDistanceSource));
    }

    public function testLoadProjectsDirsWithTemplateNotExistingProjectsDir()
    {
        $newProjectsDirPath = TEST_PROJECTS_DIR_NEW;
        $newProjectsDirName = basename($newProjectsDirPath);
        $newProjectsDirTemplate = 'nginx';

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        $projectsDirs[$newProjectsDirName] = array('path' => $newProjectsDirPath);
        $this->configurator->getConfig()->setProjectsDirs($projectsDirs);

        $this->createProject('foooo', false, $newProjectsDirName);

        $this->runCommand(
            $this->getCommand(LoadProjectsDirsCommand::class),
            [
                'name' => $newProjectsDirName,
                'template' => $newProjectsDirTemplate
            ]
        );

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal($newProjectsDirPath, $projectsDirs[$newProjectsDirName]['path']);
        Assert::equal($newProjectsDirTemplate, $projectsDirs[$newProjectsDirName]['template']);
    }

    public function testLoadProjectsDirsWithTemplateOvewriteExisting()
    {
        $projectsDirName = 'nginx';
        $changeTemplateTo = 'apache';
        $changeTemplateFrom = 'nginx';

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal($changeTemplateFrom, $projectsDirs[$projectsDirName]['template']);

        $this->createProject('foooo', false, $projectsDirName);

        $this->runCommand(
            $this->getCommand(LoadProjectsDirsCommand::class),
            [
                'name' => $projectsDirName,
                'template' => $changeTemplateTo
            ]
        );

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal($changeTemplateTo, $projectsDirs[$projectsDirName]['template']);
    }

    public function testLoadProjectsDirsWithTemplatePathNotYetInConfig()
    {
        $projectsDirName = 'nginx';
        $changeTemplateTo = 'apache';
        $changeTemplateFrom = 'nginx';

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal($changeTemplateFrom, $projectsDirs[$projectsDirName]['template']);

        $this->createProject('foooo', false, $projectsDirName);

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        $projectsDirs = array();
        $this->configurator->getConfig()->setProjectsDirs($projectsDirs);

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal(0, count($projectsDirs));

        $this->runCommand(
            $this->getCommand(LoadProjectsDirsCommand::class),
            [
                'path' => TEST_PROJECTS_DIR
            ]
        );

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        Assert::equal(1, count($projectsDirs));

    }

}

(new LoadProjectsDirsCommandTest($container))->run();
