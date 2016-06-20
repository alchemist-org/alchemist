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
    $result = [];

    // get project dir
    $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

    // check if project exists
    if(!file_exists($projectDir)) {
      throw new \Exception("Project '$projectName' does not exist and can not be removed.");
    }

    // remove project (actually)
    $result[] = Console::execute("rm -rf $projectDir");

    return $result;
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
   * @return array
   *
   * @throws \Exception
   */
  public function createProject(
    $projectName,
    $templateName = Template::DEFAULT_TEMPLATE,
    array $parameters = array()
  ) {
    $result = [];

    // use default template
    if($templateName == Template::DEFAULT_TEMPLATE) {
      $templateName = $this->configurator->getConfig()->getTemplate();
    }

    // load template
    $template = $templateName ? $this->templateLoader->getTemplate($templateName, $parameters) : null;

    // load projectDir
    $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

    // duplicates are not allowed
    if(file_exists($projectDir)) {
      throw new \Exception("Duplicates are not allowed, project with name: $projectName and dir: 
      $projectDir already exists");
    }

    // replacement parameters
    if($template) {
      $replacementParameters = $template->getParameters();
      $replacementParameters['project-name'] = $projectName;
    }

    // run before_create
    if($template) {
      $result[self::BEFORE_CREATE] = $this->runScript($template->getScript(self::BEFORE_CREATE),
        $replacementParameters);
    } else {
      $result[self::BEFORE_CREATE] = [];
    }

    // create project (actually)
    $result[self::CREATE] = Console::execute("mkdir $projectDir");

    // run after create
    if($template) {
      $result[self::AFTER_CREATE] = $this->runScript($template->getScript(self::AFTER_CREATE), $replacementParameters);
    } else {
      $result[self::AFTER_CREATE] = [];
    }

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
