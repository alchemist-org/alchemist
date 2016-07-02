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
    const TEST_ORIGIN_SOURCE = 'fooType';

    /** @var array */
    private $config = array(
        'parameters' => array(
            'projects-dir' => self::PROJECTS_DIR,
            'origin-source' => array(
                'types' => array(
                    self::TEST_ORIGIN_SOURCE => array(
                        'mkdir <project-dir>/<project-name>',
                        'echo <name>'
                    )
                )
            )
        ),
        'core' => array(
            'template' => 'common',
            'templates' => __DIR__ . '/data/templates'
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
        $this->configurator->setConfig(new Config($this->config));

        $this->manager = $this->container->getByType(Manager::class);
    }

    public function testCreateProject()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $result = $this->manager->createProject('foo');

            $this->assertManagerCreateProjectResult(
                $this->createManagerCreateProjectResult(
                    [],
                    [],
                    [],
                    [
                        0 => [],
                        1 => [
                            0 => "Project '$projectName' was successfully created."
                        ]
                    ]
                ),
                $result
            );
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'www'));
        });
    }

    public function testCreateProjectSetDefaultTemplateConst()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $result = $this->manager->createProject('foo', Template::DEFAULT_TEMPLATE);

            $this->assertManagerCreateProjectResult(
                    $this->createManagerCreateProjectResult(
                        [],
                        [],
                        [],
                        [
                            0 => [],
                            1 => [
                                0 => "Project '$projectName' was successfully created."
                            ]
                        ]
                    ),
                $result
            );
            Assert::true(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'www'));
        });
    }

    public function testCreateProjectSetNoTemplate()
    {
        $projectName = 'foo';
        Assert::noError(function () use ($projectName) {
            $result = $this->manager->createProject('foo', null);
            $this->assertManagerCreateProjectResult(
                $this->createManagerCreateProjectResult(),
                $result
            );
            Assert::false(file_exists(self::PROJECTS_DIR . DIRECTORY_SEPARATOR . $projectName . DIRECTORY_SEPARATOR . 'www'));
        });
    }

    public function testRemoveProject()
    {
        Assert::noError(function () {
            $this->manager->createProject('foo');
            $result = $this->manager->removeProject('foo');
            $expectedResult = [[]];
            Assert::equal($expectedResult, $result);
        });
    }

    public function testRemoveProjectWhichDoesNotExist()
    {
        $projectName = 'foo';
        Assert::error(function () use ($projectName) {
            $this->manager->removeProject($projectName);
        },
            '\Exception',
            "Project '$projectName' does not exist and can not be removed.");
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
            $result = $this->manager->createProject(
                $projectName,
                null,
                array(
                    'origin-source' => array(
                        'type' => self::TEST_ORIGIN_SOURCE,
                        'name' => $originSourceName,
                    )
                )
            );
            $this->assertManagerCreateProjectResult(
                $this->createManagerCreateProjectResult(
                    [],
                    [],
                    [
                        0 => [],
                        1 => [
                            0 => "$originSourceName"
                        ]
                    ],
                    []
                ),
                $result
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
            '\Exception',
            'Origin source \'noExistType\' does not exist.'
        );
    }

    /**
     * @param array $beforeCreate
     * @param array $create
     * @param array $createOriginSource
     * @param array $afterCreate
     *
     * @return array
     */
    private function createManagerCreateProjectResult($beforeCreate = [], $create = [], $createOriginSource = [], $afterCreate = [])
    {
        return array(
            Manager::BEFORE_CREATE => $beforeCreate,
            Manager::CREATE => $create,
            Manager::CREATE_ORIGIN_SOURCE => $createOriginSource,
            Manager::AFTER_CREATE => $afterCreate
        );
    }

    private function assertManagerCreateProjectResult($expectedResult, $result)
    {
        Assert::equal($expectedResult[Manager::BEFORE_CREATE], $result[Manager::BEFORE_CREATE]);
        Assert::equal($expectedResult[Manager::CREATE], $result[Manager::CREATE]);
        Assert::equal($expectedResult[Manager::CREATE_ORIGIN_SOURCE], $result[Manager::CREATE_ORIGIN_SOURCE]);
        Assert::equal($expectedResult[Manager::AFTER_CREATE], $result[Manager::AFTER_CREATE]);
    }

}

$testCase = new ManagerTest($container);
$testCase->run();
