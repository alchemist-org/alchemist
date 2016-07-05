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

  /** @var string */
  const DEFAULT_LOCAL_CONFIG_FILE = __DIR__ .'/Config/config.local.neon';

  /**
   * Configurator constructor.
   *
   * @param string $configFile
   */
  public function __construct($configFile = null) {
    $this->configFile = $configFile ? $configFile : self::DEFAULT_LOCAL_CONFIG_FILE;
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
   * @param bool $save
   */
  public function setConfig(Config $config, $save = true) {
    $this->config = $config;

    if($save) {
      $this->saveConfigData();
    }
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

    return $config ? $config : array();
  }

  private function saveConfigData() {
    if(file_exists($this->configFile)) {
      $configData = $this->config->getConfig();

      // TODO: filter null values

      // TODO: filter default values which was not overwrited

      $content = Neon::encode($configData, array(Neon::BLOCK));
      file_put_contents($this->configFile, $content);
    }
  }

}
