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
use Nette\Neon\Neon;

$container = require_once __DIR__ . '/../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class ManagerTest extends TestCase
{

    /** @var string */
    const PROJECTS_DIR_NAME = 'defaultt';
    const PROJECTS_DIR2_NAME = 'defaultt2';

    /** @var string */
    const CONFIG_LOCAL = __DIR__ . '/data/config/config.local.neon';
    /** @var string */
    const HOSTS_FILE = __DIR__ . '/data/hosts';
    /** @var string */
    const NGINX_SITES_ENABLED = __DIR__ . '/data/nginx/sites-enabled';
    /** @var string */
    const APACHE_SITES_ENABLED = __DIR__ . '/data/apache/sites-enabled';
    /** @var string */
    const TLD = 'dev';
    /** @var integer */
    const PORT = 80;
    /** @var string */
    const ROOT = 'www';
    /** @var string */
    const LOCALHOST = '127.0.0.1';
    /** @var string */
    const APP_DATA_DIR = __DIR__ . '/../../data';

    /** @var string */
    const TEST_PROJECT = 'test';
    const TEST_PROJECT_2 = 'test_2';

    /** @var string */
    const TEST_PROJECT_SOURCE_TYPE = 'fooType';

    /** @var string */
    const DEFAULT_GIT_EMAIL = 'super@user.com';
    const DEFAULT_GIT_NAME = 'super user';

    /** @var string */
    const COMPANY_A_GIT_NAME = 'super user - company A';
    const COMPANY_A_GIT_EMAIL = 'test@company.a';

    /** @var Manager */
    private $manager;

    /** @var Configurator */
    private $configurator;

    /** @var Container */
    private $container;

    /** @var array */
    private $config = [
        'parameters' => [
            'projects-dir' => self::PROJECTS_DIR_NAME,
            'origin-source' => [],
            'test' => 'test',
            'hosts' => self::HOSTS_FILE,
            'tld' => self::TLD,
            'localhost' => self::LOCALHOST,
            'nginx-sites-enabled' => self::NGINX_SITES_ENABLED,
            'nginx-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/nginx.default',
            'apache-sites-enabled' => self::APACHE_SITES_ENABLED,
            'apache-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/apache.default',
            'port' => self::PORT,
            'root' => self::ROOT
        ],
        'after_create' => [
            "cd <project-dir> && git init",
            "cd <project-dir> && git config user.name '" . self::DEFAULT_GIT_NAME . "'",
            "cd <project-dir> && git config user.email '" . self::DEFAULT_GIT_EMAIL . "'",
        ],
        'core' => [
            'template' => 'common',
            'templates' => __DIR__ . '/data/templates',
            'source-types' => [
                self::TEST_PROJECT_SOURCE_TYPE => [
                    'mkdir <project-dir>/<project-name>',
                    'echo <project-name>'
                ]
            ],
            'projects-dirs' => [
                self::PROJECTS_DIR_NAME => [
                    'path' => TEST_PROJECTS_DIR,
                    'template' => 'nginx'
                ],
                self::PROJECTS_DIR2_NAME => [
                    'path' => TEST_PROJECTS_DIR2,
                    'template' => 'apache'
                ],
             ],
            'test' => [
                'test' => [
                    'test' => [
                        'test' => []
                    ]
                ]
            ]
        ]
    ];

    /**
     * ManagerTest constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setUp()
    {
        parent::setUp();

        $this->clean();

        $this->configurator = $this->container->getByType(Configurator::class);
        $this->manager = $this->container->getByType(Manager::class);
        $this->configurator->setConfigFile(self::CONFIG_LOCAL);
        $this->configurator->setConfig(new Config($this->config));
    }

    public function tearDown()
    {
        parent::tearDown();

        $this->clean();
    }

    public function clean()
    {
        \Tester\Helpers::purge(TEST_PROJECTS_DIR);
        \Tester\Helpers::purge(TEST_PROJECTS_DIR2);
        \Tester\Helpers::purge(TEST_TEMP_DIR);

        // nginx files
        foreach(glob(self::NGINX_SITES_ENABLED . '/*') as $file) {
            if(is_file($file) && $file != '.gitkeep') {
                unlink($file);
            }
        }

        // apache files
        foreach(glob(self::APACHE_SITES_ENABLED . '/*') as $file) {
            if(is_file($file) && $file != '.gitkeep') {
                unlink($file);
            }
        }

        // hosts
        if(is_file(self::HOSTS_FILE)) {
            unlink(self::HOSTS_FILE);
        }

        // config local
        if(is_file(self::CONFIG_LOCAL)) {
            unlink(self::CONFIG_LOCAL);
        }
    }

    public function testCreateProject()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
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

        Assert::noError(function() use ($projectName) {
            $projectDir = TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName;

            $this->manager->createProject($projectName,
                [
                    'company.test'
                ]);
            Assert::true(file_exists($projectDir));
            Assert::equal('true', exec("cd " . $projectDir . " && git rev-parse --is-inside-work-tree"));
            Assert::equal(self::COMPANY_A_GIT_NAME, exec("cd " . $projectDir . " && git config user.name"));
            Assert::equal(self::COMPANY_A_GIT_EMAIL, exec("cd " . $projectDir . " && git config user.email"));
        });
    }

    public function testCreateProjectAndForceRecreateNew()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, [], null, false);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
            $this->manager->createProject($projectName, null, [], null, true);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
            Assert::false(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectAndCheckSave()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            Assert::equal(TEST_PROJECTS_DIR, $this->configurator->getConfig()->getProjectsDir());
            $this->manager->createProject($projectName,
                Template::DEFAULT_TEMPLATE,
                [
                    'projects-dir' => TEST_PROJECTS_DIR
                ],
                true);
            $result = $this->configurator->getConfig()->getProjectsDir();
            Assert::equal(TEST_PROJECTS_DIR, $result);
            Assert::truthy($this->configurator->getConfig()->getProjectsDir());
            $result = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
            Assert::truthy($result);
            Assert::truthy($this->configurator->getConfig()
                ->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
        });
    }

    public function testCreateProjectAndCheckSaveNotSave()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, [], false);
            Assert::falsey($this->configurator->getConfig()->getDistantSource($projectName));
        });
    }

    public function testCreateProjectAndChangeProjectDirViaNameInConsole()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName,
                Template::DEFAULT_TEMPLATE,
                [
                    'projects-dir' => TEST_PROJECTS_DIR2
                ],
                false);
            Assert::true(file_exists(TEST_PROJECTS_DIR2 . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testCreateProjectAndChangeProjectDirViaFullPathInConsole()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName,
                Template::DEFAULT_TEMPLATE,
                [
                    'projects-dir' => TEST_PROJECTS_DIR
                ],
                false);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testTouchProject()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $result1 = $this->manager->createProject($projectName);
            Assert::truthy($result1);
            $result2 = $this->manager->touchProject($projectName);
            Assert::truthy($result2);
        });
    }

    public function testCreateProjectSetDefaultTemplateConst()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE);
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testCreateProjectSetNoTemplate()
    {
        $projectName = 'foo';

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, null);
            Assert::false(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'after_create'));
        });
    }

    public function testRemoveProject()
    {
        $projectName = self::TEST_PROJECT;

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, null, [], true);
            Assert::truthy($this->configurator->getConfig()
                ->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
            Assert::truthy($this->manager->touchProject($projectName));
            $this->manager->removeProject($projectName, true);
            Assert::falsey($this->manager->touchProject($projectName));
            Assert::truthy(!isset($this->configurator->getConfig()
                    ->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]));
        });
    }

    public function testRemoveProjectNoSave()
    {
        $projectName = self::TEST_PROJECT;

        Assert::noError(function() use ($projectName) {
            $this->manager->createProject($projectName, Template::DEFAULT_TEMPLATE, [], true);
            Assert::truthy($this->configurator->getConfig()
                ->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
            Assert::truthy($this->manager->touchProject($projectName));
            $this->manager->removeProject($projectName);
            Assert::truthy($this->configurator->getConfig()
                ->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE)[$projectName]);
        });
    }

    public function testRemoveProjectWhichDoesNotExist()
    {
        $projectName = 'foo';

        Assert::error(function() use ($projectName) {
            $this->manager->removeProject($projectName);
        },
            '\Exception');
    }

    public function testDuplicatesExpectsException()
    {
        $projectName = 'foo';

        $this->manager->createProject($projectName);
        Assert::exception(function() use ($projectName) {
            $this->manager->createProject($projectName);
        },
            '\Exception');
    }

    public function testCreateProjectFromDistantSource()
    {
        $projectName = 'distant';
        $originSourceName = 'originSourceName';

        Assert::noError(function() use ($projectName, $originSourceName) {
            $this->manager->createProject(
                $projectName,
                null,
                [
                    'origin-source' => [
                        'type' => self::TEST_PROJECT_SOURCE_TYPE,
                        'name' => $originSourceName,
                    ]
                ]
            );
            Assert::true(file_exists(TEST_PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . $projectName));
        });
    }

    public function testCreateProjectFromDistantSourceWhichDoesNotExist()
    {
        $projectName = 'distant';

        Assert::error(function() use ($projectName) {
            $this->manager->createProject(
                $projectName,
                null,
                [
                    'origin-source' => [
                        'type' => 'noExistType'
                    ]
                ]
            );
        },
            '\Exception'
        );
    }

    public function testInstall()
    {
        $projectName = 'test';

        $this->manager->createProject(
            $projectName
        );

        $this->manager->install();

        Assert::truthy($this->manager->touchProject($projectName));
    }

    public function testTouchProjects()
    {
        $projectName = 'fooooooooo';
        $projectName2 = 'fooooooooo2';

        Assert::falsey($this->manager->touchProjects($projectName));
        Assert::falsey($this->manager->touchProjects($projectName2));

        $this->manager->createProject($projectName, 'nginx', array(), true);

        $results = $this->manager->touchProjects($projectName);
        Assert::truthy($results);

        $this->manager->createProject($projectName2, 'apache', array(), true);

        $results2 = $this->manager->touchProjects();
        Assert::truthy($results2);

        $this->manager->removeProject($projectName);
        $this->manager->removeProject($projectName2);

        $result3 = $this->manager->touchProjects();
        Assert::falsey($result3);
    }

    public function testSaveAndInstall()
    {
        $projectName = 'testSaveCommand';

        $distantSources = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::falsey($distantSources);

        $this->manager->createProject($projectName, ['common', 'empty', 'empty2'], [], true);

        $this->manager->save();

        $result = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy($result);
    }

    public function testBugNotReaoadingConfigParameters()
    {
        $projectName1 = 'foo';
        $projectName2 = 'bar';

        $this->manager->createProject($projectName1, null, array('root' => 'juchu'));

        $this->manager->createProject($projectName2, null);

        Assert::notEqual('juchu', $this->configurator->getConfig()->getParameter('root'));
    }

}

(new ManagerTest($container))->run();
