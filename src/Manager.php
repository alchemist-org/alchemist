<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Manager {

  /** @var Configurator */
  private $configurator;

  /** @var TemplateLoader */
  private $templateLoader;

  /** @var string */
  const BEFORE_CREATE = 'before_create';

  /** @var string */
  const AFTER_CREATE = 'after_create';

  /** @var string */
  const CREATE = 'create';

  /**
   * Manager constructor.
   *
   * @param Configurator $configurator
   * @param TemplateLoader $templateLoader
   */
  public function __construct(Configurator $configurator, TemplateLoader $templateLoader) {
    $this->configurator = $configurator;
    $this->templateLoader = $templateLoader;
  }

  /**
   * @param string $projectName
   *
   * @return void
   */
  public function removeProject($projectName) {
    // get project dir
    $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

    // check if project exists
    if(!file_exists($projectDir)) {
      throw new \Exception("Project '$projectName' does not exist and can not be removed.");
    }

    // remove project (actually)
    Console::execute("rm -rf $projectDir");
  }

  /**
   * @param string $projectName
   * @param string $projectsDir
   *
   * @return string
   */
  private function getProjectDir($projectName, $projectsDir) {
    return $projectsDir.DIRECTORY_SEPARATOR.$projectName;
  }

  /**
   * @param string $projectName
   * @param string|null $templateName
   * @param array $parameters
   *
   * @throws \Exception
   */
  public function createProject(
    $projectName,
    $templateName = null,
    array $parameters = array()
  ) {
    $result = [];

    // use template, default or own
    $templateName = $templateName ? $templateName : $this->configurator->getConfig()->getTemplate();

    // load template
    $template = $this->templateLoader->getTemplate($templateName, $parameters);

    // load projectDir
    $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

    // duplicates are not allowed
    if(file_exists($projectDir)) {
      throw new \Exception("Duplicates are not allowed, project with name: $projectName and dir: 
      $projectDir already exists");
    }

    // replacement parameters
    $replacementParameters = $template->getParameters();
    $replacementParameters['project-name'] = $projectName;

    // run before_create
    $result[self::BEFORE_CREATE] = $this->runScript($template->getScript(self::BEFORE_CREATE), $replacementParameters);

    // create project (actually)
    $result[self::CREATE] = Console::execute("mkdir $projectDir");

    // run after create
    $result[self::AFTER_CREATE] = $this->runScript($template->getScript(self::AFTER_CREATE), $replacementParameters);

    return $result;
  }

  /**
   * @param array $script
   * @param array $replaceParameters
   *
   * @return array
   */
  public function runScript(array $script, $replaceParameters = array()) {
    $result = [];
    foreach($script as $index => $scriptLine) {
      $scriptLine = Parser::parse($scriptLine, $replaceParameters);
      $result[] = Console::execute($scriptLine);
    }
    return $result;
  }

}
