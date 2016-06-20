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

use Nette\Neon\Neon;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Configurator {

  /** @var Config */
  private $config = null;

  /** @var null|string */
  private $configFile = null;

  /**
   * Configurator constructor.
   *
   * @param string $configFile
   */
  public function __construct($configFile = null) {
    $this->configFile = $configFile;
  }

  /**
   * @param string $configFile
   *
   * @return void
   */
  public function setConfigFile($configFile) {
    $this->configFile = $configFile;
  }

  /**
   * @param Config $config
   */
  public function setConfig(Config $config) {
    $this->config = $config;
  }

  /**
   * @return Config
   *
   * @throws \Exception
   */
  public function getConfig() {
    if($this->config == null) {
      $this->config = new Config($this->loadConfigData());
    }

    if($this->config == null) {
      throw new \Exception("Config is missing.");
    }
    return $this->config;
  }

  /**
   * @return mixed
   *
   * @throws \Exception
   */
  private function loadConfigData() {
    if(!file_exists($this->configFile)) {
      throw new \Exception("Config file '$this->configFile' does not exist.");
    }

    $contents = file_get_contents($this->configFile);
    $config = Neon::decode($contents);

    return $config;
  }

}
