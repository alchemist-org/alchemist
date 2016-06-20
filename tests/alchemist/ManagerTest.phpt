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

$container = require_once __DIR__.'/../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class ManagerTest extends TestCase {

  /** @var Manager */
  private $manager;

  /** @var Configurator */
  private $configurator;

  /** @var Container */
  private $container;

  /** @var array */
  private $config = array(
    'parameters' => array(
      'projects-dir' => TEMP__DIR__.'/projects-dir'
    ),
    'core' => array(
      'template' => 'common',
      'templates' => __DIR__.'/data/templates'
    )
  );

  /** @var string */
  const PROJECTS_DIR = TEMP__DIR__.'/projects-dir';

  public function __construct(Container $container) {
    $this->container = $container;
  }

  protected function setUp() {
    parent::setUp();

    @mkdir(self::PROJECTS_DIR);
    \Tester\Helpers::purge(self::PROJECTS_DIR);

    $this->configurator = $this->container->getByType(Configurator::class);
    $this->configurator->setConfig(new Config($this->config));

    $this->manager = $this->container->getByType(Manager::class);
  }

  public function testCreateProject() {
    $projectName = 'foo';
    Assert::noError(function() use ($projectName) {
      $result = $this->manager->createProject('foo');
      $expectedResult = [
        Manager::BEFORE_CREATE => [],
        Manager::CREATE => [],
        Manager::AFTER_CREATE => [
          0 => [],
          1 => [
            0 => "Project '$projectName' was successfully created."
          ]
        ]
      ];
      Assert::equal($expectedResult, $result);
      Assert::true(file_exists(self::PROJECTS_DIR.DIRECTORY_SEPARATOR.$projectName.DIRECTORY_SEPARATOR.'www'));
    });
  }

  public function testCreateProjectSetDefaultTemplateConst() {
    $projectName = 'foo';
    Assert::noError(function() use ($projectName) {
      $result = $this->manager->createProject('foo', Template::DEFAULT_TEMPLATE);
      $expectedResult = [
        Manager::BEFORE_CREATE => [],
        Manager::CREATE => [],
        Manager::AFTER_CREATE => [
          0 => [],
          1 => [
            0 => "Project '$projectName' was successfully created."
          ]
        ]
      ];
      Assert::equal($expectedResult, $result);
      Assert::true(file_exists(self::PROJECTS_DIR.DIRECTORY_SEPARATOR.$projectName.DIRECTORY_SEPARATOR.'www'));
    });
  }

  public function testCreateProjectSetNoTemplate() {
    $projectName = 'foo';
    Assert::noError(function() use ($projectName) {
      $result = $this->manager->createProject('foo', null);
      $expectedResult = [
        Manager::BEFORE_CREATE => [],
        Manager::CREATE => [],
        Manager::AFTER_CREATE => []
      ];
      Assert::equal($expectedResult, $result);
      Assert::false(file_exists(self::PROJECTS_DIR.DIRECTORY_SEPARATOR.$projectName.DIRECTORY_SEPARATOR.'www'));
    });
  }

  public function testRemoveProject() {
    Assert::noError(function() {
      $this->manager->createProject('foo');
      $this->manager->removeProject('foo');
    });
  }

  public function testRemoveProjectWhichDoesNotExist() {
    $projectName = 'foo';
    Assert::error(function() use ($projectName) {
      $this->manager->removeProject($projectName);
    },
      '\Exception',
      "Project '$projectName' does not exist and can not be removed.");
  }

  public function testDuplicatesExpectsException() {
    $this->manager->createProject('foo');
    Assert::exception(function() {
      $this->manager->createProject('foo');
    },
      '\Exception');
  }

  public function testCreateProjectChangeProjectsDir() {
    Assert::noError(function() {
      $this->manager->createProject('empty', 'empty');
    });
  }

}

$testCase = new ManagerTest($container);
$testCase->run();
