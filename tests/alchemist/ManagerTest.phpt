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
    const PROJECTS_DIR_NAME = 'defaultt';

    /** @var string */
    const CONFIG_LOCAL = __DIR__ . '/data/config/config.local.neon';

    /** @var string */
    const TEST_PROJECT = 'test';

    /** @var string */
    const TEST_PROJECT_SOURCE_TYPE = 'fooType';

    /** @var string */
    const DEFAULT_GIT_EMAIL = 'super@user.com';
    const DEFAULT_GIT_NAME = 'super user';

    /** @var string */
    const COMPANY_A_GIT_NAME = 'super user - company A';
    const COMPANY_A_GIT_EMAIL = 'test@company.a';

    /** @var array */
    private $config = array(
        'parameters' => array(
            'projects-dir' => self::PROJECTS_DIR_NAME,
            'origin-source' => array(),
            'test' => 'test'
        ),
        'after_create' => array(
            "cd <project-dir> && git init",
            "cd <project-dir> && git config user.name '" . self::DEFAULT_GIT_NAME . "'",
            "cd <project-dir> && git config user.email '" . self::DEFAULT_GIT_EMAIL . "'",
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
               self::PROJECTS_DIR_NAME => TEST_PROJECTS_DIR
            ),
            'test' => array(
                'test' => array(
                    'test' => array(
                        'test' => array()
                    )
                )
            )
        ),
        'distant-sources' => array(
            'default' => array(),
            'github' => array(
                self::TEST_PROJECT => array(
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

        $this->configurator = $this->container->getByType(Configurator::class);

        // clear test directories
        if(!file_exists(TEST_PROJECTS_DIR)) {
            mkdir(TEST_PROJECTS_DIR);
        } else {
            \Tester\Helpers::purge(TEST_PROJECTS_DIR);
        }

        if(!file_exists(TEST_TEMP_DIR)) {
            mkdir(TEST_TEMP_DIR);
        } else {
            \Tester\Helpers::purge(TEST_TEMP_DIR);
        }

        // set array (because const used above in tests) but self::CONFIG_LOCAL reflect any change
        $this->configurator->setConfigFile(self::CONFIG_LOCAL);
        $this->configurator->setConfig(new Config($this->config));

        $this->manager = $this->container->getByType(Manager::class);
    }

    public function testCreateProject()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;

            $this->manager->createProject($projectName);
            Assert::true(file_exists($projectDir));
            Assert::true(file_exists($projectDir . DIRECTORY_SEPARATOR . 'after_create'));
            Assert::equal(self::DEFAULT_GIT_EMAIL, exec("cd " . $projectDir . " && git config user.email"));
            Assert::equal(self::DEFAULT_GIT_NAME, exec("cd " . $projectDir . " && git config user.name"));
        });
    }

    public function testCreateProjectMoreTemplatesAndAnotherGitSetUp()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;

            $this->manager->createProject($projectName, array(
                'company.test'
            ));
            Assert::true(file_exists($projectDir));
            Assert::equal('true', exec("cd " . $projectDir . " && git rev-parse --is-inside-work-tree"));
            Assert::equal(self::COMPANY_A_GIT_NAME, exec("cd " . $projectDir . " && git config user.name"));
            Assert::equal(self::COMPANY_A_GIT_EMAIL, exec("cd " . $projectDir . " && git config user.email"));
        });
    }

    public function testCreateProjectAndForceRecreateNew()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(), null, false);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
            $this->manager->createProject($projectName, null, array(), null, true);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::false(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectAndCheckSave()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            Assert::equal(TEST_PROJECTS_DIR, $this->configurator->getConfig()->getProjectsDir());
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(
                'projects-dir' => TEST_PROJECTS_DIR
            ), true);
            Assert::equal(TEST_PROJECTS_DIR, $this->configurator->getConfig()->getProjectsDir());
            Assert::truthy($this->configurator->getConfig()->getProjectsDir());
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
                'projects-dir' => TEST_PROJECTS_DIR
            ), false);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testCreateProjectAndChangeProjectDirViaFullPathInConsole()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(
                'projects-dir' => TEST_PROJECTS_DIR
            ), false);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
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
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectSetNoTemplate()
    {
        $projectName = 'foo';

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, null);
            Assert::false(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testRemoveProject()
    {
        $projectName = self::TEST_PROJECT;

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, null, array(), true);
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
            Assert::truthy($this->manager->touchProject($projectName));
            $this->manager->removeProject($projectName, true);
            Assert::falsey($this->manager->touchProject($projectName));
            Assert::truthy(!isset($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]));
        });
    }

    public function testRemoveProjectNoSave()
    {
        $projectName = self::TEST_PROJECT;

        Assert::noError(function () use ($projectName) {
            $this->manager->createProject($projectName, null, array(), true);
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
            Assert::truthy($this->manager->touchProject($projectName));
            $this->manager->removeProject($projectName);
            Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
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
        $projectName = 'foo';

        $this->manager->createProject($projectName);
        Assert::exception(function () use ($projectName) {
            $this->manager->createProject($projectName);
        },
            '\Exception');
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
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . $projectName));
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
        $projectName = 'test';

        $this->manager->install();

        Assert::truthy($this->manager->touchProject($projectName));
    }

    public function testTouchProjects()
    {
        $projectName = 'fooooooooo';

        Assert::falsey($this->manager->touchProjects($projectName));

        $this->manager->createProject($projectName);

        Assert::truthy($this->manager->touchProjects($projectName));

        $this->manager->removeProject($projectName);

        Assert::falsey($this->manager->touchProjects($projectName));
    }

    public function testSaveAndInstall()
    {
        $projectName = 'testSaveCommand';

        Assert::falsey($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE));

        $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, array(), true);

        $this->manager->save();

        Assert::truthy($this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE));
    }

}

$testCase = new ManagerTest($container);
$testCase->run();
