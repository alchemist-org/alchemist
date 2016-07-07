<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests;

use Alchemist\Config;
use Alchemist\Configurator;
use Alchemist\DistantSource;
use Alchemist\Manager;
use Alchemist\Template;
use Nette\DI\Container;
use Tester\Assert;
use Tester\TestCase;

$container = require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class ManagerTest extends TestCase
{
    /** @var Manager */
    private $manager;

    /** @var Configurator */
    private $configurator;

    /** @var Container */
    private $container;

    /** @var string */
    const PROJECTS_DIR = TEMP__DIR__ . '/projects-dir';

    /** @var string */
    const PROJECT_DIR_NAME = 'default';

    /** @var string */
    const CONFIG_LOCAL = __DIR__ . '/data/config/config.local.neon';

    /** @var string */
    const TEST_PROJECT = 'test';

    /** @var string */
    const TEST_PROJECT_SOURCE_TYPE = 'fooType';

    /** @var array */
    private $config = array(
        'parameters' => array(
            'projects-dir' => self::PROJECT_DIR_NAME,
            'origin-source' => array()
        ),
        'core' => array(
            'template' => 'common',
            'templates' => __DIR__ . '/data/templates',
            'source-types' => array(
                self::TEST_PROJECT_SOURCE_TYPE => array(
                    'mkdir <project-dir>/<project-name>',
                    'echo <project-name>'
                )
            ),
            'projects-dirs' => array(
               self::PROJECT_DIR_NAME => self::PROJECTS_DIR
            )
        ),
        'distant-sources' => array(
            'default' => array(),
            'github' => array(
                self::TEST_PROJECT => array(
                    'projects-dir' => 'default',
                    'template' => 'common',
                    'origin-source' => array(
                        'type' => self::TEST_PROJECT_SOURCE_TYPE
                    )
                )
            )
        )
    );

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function setUp()
    {
        parent::setUp();

        @mkdir(self::PROJECTS_DIR);
        \Tester\Helpers::purge(self::PROJECTS_DIR);

        $this->configurator = $this->container->getByType(Configurator::class);

        // set array (because const used above in tests) but self::CONFIG_LOCAL reflect any change
        $this->configurator->setConfig(new Config($this->config));
        $this->configurator->setConfigFile(self::CONFIG_LOCAL);

        $this->manager = $this->container->getByType(Manager::class);
    }

   /* public function testCreateProject()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }*/

    public function testCreateProjectAndForceRecreateNew()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(), null, false);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
            $this->manager->createProject($projectName, null, array(), null, true);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::false(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectAndCheckSave()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(
                'projects-dir' => self::PROJECTS_DIR
            ), true);
            Assert::truthy($this->configurator->getConfig()->getProjectsDirPath('projects-dir'));
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE));
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
        });
    }

    public function testCreateProjectAndCheckSaveNotSave()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(), false);
            Assert::falsey($this->configurator->getConfig()->getDistantSource($projectName));
        });
    }

    public function testCreateProjectAndChangeProjectDirViaNameInConsole()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(
                'projects-dir' => 'default'
            ), false);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testCreateProjectAndChangeProjectDirViaFullPathInConsole()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(
                'projects-dir' => self::PROJECTS_DIR
            ), false);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testTouchProject()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName);
            $result = $this->manager->touchProject($projectName);
            Assert::truthy($result);
        });
    }

    public function testCreateProjectSetDefaultTemplateConst()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject('foo', Template::DEFAULT_TEMPLATE);
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectSetNoTemplate()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $this->manager->createProject('foo', null);
            Assert::false(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testRemoveProject()
    {
        $projectName = self::TEST_PROJECT;

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, null, array(), true);
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
            Assert::truthy($this->manager->touchProject($projectName));
            $this->manager->removeProject($projectName);
            Assert::falsey($this->manager->touchProject($projectName));
            Assert::falsey($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
        });
    }

    public function testRemoveProjectWhichDoesNotExist()
    {
        $projectName = 'foo';
        Assert::error(function () use ($projectName) {
            $this->manager->removeProject($projectName);
        },
            '\Exception');
    }

    public function testDuplicatesExpectsException()
    {
        $this->manager->createProject('foo');
        Assert::exception(function () {
            $this->manager->createProject('foo');
        },
            '\Exception');
    }

    public function testCreateProjectChangeProjectsDir()
    {
        Assert::noError(function () {
            $this->manager->createProject('empty', 'empty');
        });
    }

    public function testCreateProjectFromDistantSource()
    {
        $projectName = 'distant';
        $originSourceName = 'originSourceName';

        Assert::noError(function () use ($projectName, $originSourceName) {
            $this->manager->createProject(
                $projectName,
                null,
                array(
                    'origin-source' => array(
                        'type' => self::TEST_PROJECT_SOURCE_TYPE,
                        'name' => $originSourceName,
                    )
                )
            );
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testCreateProjectFromDistantSourceWhichDoesNotExist()
    {
        $projectName = 'distant';
        Assert::error(function () use ($projectName) {
            $this->manager->createProject(
                $projectName,
                null,
                array(
                    'origin-source' => array(
                        'type' => 'noExistType'
                    )
                )
            );
        },
            '\Exception'
        );
    }

    public function testInstall()
    {
        $this->manager->install();
        Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . 'test'));
    }

}

$testCase = new ManagerTest($container);
$testCase->run();
