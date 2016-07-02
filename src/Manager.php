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
use Alchemist\Utils\Arrays;

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

  /** @var string */
  const CREATE_ORIGIN_SOURCE = 'create_origin_source';

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
   * @return array
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
   * @param string|Template::DEFAULT_TEMPLATE|null $templateName
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
      $templateName = $this->configurator->getConfig()->getTemplateName();
    }

    // merge config parameters with console parameters
    $parameters = Arrays::merge($this->configurator->getConfig()->getParameters(), $parameters);

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
    $replacementParameters = $template ? $template->getParameters() : $this->configurator->getConfig()->applyConsoleParameters($parameters);

    // add common replacement parameters
    $replacementParameters['project-name'] = $projectName;
    $replacementParameters['project-dir'] = $projectDir;

    // run before_create
    if($template) {
      $result[self::BEFORE_CREATE] = $this->runScript($template->getScript(self::BEFORE_CREATE),
        $replacementParameters);
    } else {
      $result[self::BEFORE_CREATE] = [];
    }

    // create project (actually)
    $result[self::CREATE] = Console::execute("mkdir $projectDir");

    // load origin source
    $originSource = $template && isset($template->getParameters()['origin-source']) ? $template->getParameter('origin-source') : $this->configurator->getConfig()->getParameter('origin-source');

    // load project from origin source (actually)
    $originSourceType = $originSource['type'];
    if($originSourceType) {

      if (!isset($originSource['types'][$originSourceType])) {
        throw new \Exception("Origin source '$originSourceType' does not exist.");
      }

      // add specific replacement parameters
      $originSourceParameters = $originSource;
      unset($originSourceParameters['types']);
      $replacementParametersOriginSource = Arrays::merge($replacementParameters, $originSourceParameters);

      $result[self::CREATE_ORIGIN_SOURCE] = $this->runScript($originSource['types'][$originSourceType], $replacementParametersOriginSource);
    } else {
      $result[self::CREATE_ORIGIN_SOURCE] = [];
    }

    // run after create
    if($template) {
      $result[self::AFTER_CREATE] = $this->runScript($template->getScript(self::AFTER_CREATE), $replacementParameters);
    } else {
      $result[self::AFTER_CREATE] = [];
    }

    return $result;
  }

  /**
   * @param array|string $script
   * @param array $replaceParameters
   *
   * @return array
   */
  public function runScript($script, $replaceParameters = array()) {
    $script = is_string($script) ? array($script) : $script;

    $result = [];
    foreach($script as $index => $scriptLine) {
      $scriptLine = Parser::parse($scriptLine, $replaceParameters);
      $result[] = Console::execute($scriptLine);
    }
    return $result;
  }

}
