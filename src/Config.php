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
use Nette\Object;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Config extends Object {

  /** @var array */
  private $config;

  /** @var array */
  private $default = array(
    'parameters' => array(
      'projects-dir' => null  #required ['projects location']
    ),
    'core' => array(
      'template' => null,   #optional ['by default is template not set']
      'templates' => null   #optional ['templates folder is required when is needed']
    )
  );

  /**
   * @param array $config
   */
  public function __construct(array $config) {
    $this->config = Arrays::merge($this->default, $config);
  }

  public function getTemplate() {
    return $this->config['core']['template'];
  }

  public function getTemplates() {
    if(!isset($this->config['core']['templates'])) {
      throw new \Exception("Config parameter 'templates' is required.");
    }
    return $this->config['core']['templates'];
  }

  public function getProjectsDir() {
    if(!isset($this->config['parameters']['projects-dir'])) {
      throw new \Exception("Config parameter 'projects-dir' is required.");
    }
    return $this->config['parameters']['projects-dir'];
  }

  public function getParameters() {
    return $this->config['parameters'];
  }

}