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
class Config extends Object
{

    /** @var array */
    private $config;

    /** @var array */
    private $default = array(
        'parameters' => array(
            'projects-dir' => null,
            'origin-source' => array(
                'type' => null,
                'value' => null
            )
        ),
        'core' => array(
            'template' => 'default',
            'templates' => __DIR__ . '/../data/templates',
            'source-types' => array(
                'composer' => 'composer create-project <value> <project-dir>',
                'git' => 'git clone <value> <project-dir>'
            ),
            'projects-dirs' => array()
        ),
        'distant-sources' => array(
            'default' => array()
        )
    );

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!isset($config['parameters']) || !isset($config['parameters']['projects-dir'])) {
            throw new \Exception("Config parameter 'projects-dir' is required.");
        }
        $this->config = Arrays::merge($this->default, $config);
    }

    public function getTemplateName()
    {
        return $this->config['core']['template'];
    }

    public function getTemplates()
    {
        return $this->config['core']['templates'];
    }

    public function setTemplates()
    {
        $this->config['core']['tepmplates'];
    }

    public function getSourceTypes()
    {
        return $this->config['core']['source-types'];
    }

    public function getSourceType($name)
    {
        return $this->config['core']['source-types'][$name];
    }

    public function getProjectsDirs()
    {
        return $this->config['core']['projects-dirs'];
    }

    public function setProjectsDirs(array $projectsDirs = array())
    {
        $this->config['core']['projects-dirs'] = $projectsDirs;
    }

    public function getProjectsDirPath($name)
    {
        if(isset($this->getProjectsDirs()[$name])) {
            return $this->getProjectsDirs()[$name];
        }
        return null;
    }

    public function getProjectsDir()
    {
        $defaultProjectsDirNameOrPath = $this->config['parameters']['projects-dir'];

        if($this->getProjectsDirPath($defaultProjectsDirNameOrPath)) {
            return $this->getProjectsDirPath($defaultProjectsDirNameOrPath);
        }

        return $defaultProjectsDirNameOrPath;
    }

    public function getParameters()
    {
        return $this->config['parameters'];
    }

    public function applyConsoleParameters(array $parameters = array())
    {
        $filteredParameters = $parameters;

        // filter ignored values
        $this->filterConfigData($filteredParameters, $this->config['parameters'], function($key, $value, $defaultValue) {
            return $this->isIgnoredValue($key, $value, $defaultValue);
        });

        return $this->config['parameters'] = Arrays::merge($this->config['parameters'], $filteredParameters);
    }

    public function getParameter($name)
    {
        return $this->config['parameters'][$name];
    }

    public function getDistantSources()
    {
        return $this->config['distant-sources'];
    }

    public function getDistantSource($name)
    {
        return isset($this->config['distant-sources'][$name]) ? $this->config['distant-sources'][$name] : null;
    }

    public function setDistantSource($name, $data)
    {
        $this->config['distant-sources'][$name] = $data;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getFilteredConfig()
    {
        $filteredConfig = $this->config;

        // filter ignored values
        $this->filterConfigData($filteredConfig, $this->default, function($key, $value, $defaultValue) {
            return $this->isIgnoredValue($key, $value, $defaultValue);
        });

        // filter default
        $this->filterConfigData($filteredConfig, $this->default, function($key, $value, $defaultValue) {
            return $this->isDefaultValue($key, $value, $defaultValue);
        });

        return $filteredConfig;
    }

    /**
     * @param array|mixed $array
     * @param callable $filterFunction
     *
     * @return bool
     */
    public function filterConfigData(&$array, $defaultArray, callable $filterFunction)
    {
        if(!is_array($array)) {
            return false;
        }

        foreach ($array as $key => &$value) {
            if(array_key_exists($key, $defaultArray)) {
                $parentNode = $this->filterConfigData($value, $defaultArray[$key], $filterFunction);

                if($parentNode) {
                    unset($array[$key]);
                }

                $result = call_user_func($filterFunction, $key, $value, $defaultArray[$key]);
                if($result) {
                    unset($array[$key]);
                }
            } else {
                $this->filterConfigData($value, array(), $filterFunction);

                $result = call_user_func($filterFunction, $key, $value, null);
                if($result) {
                    unset($array[$key]);
                }
            }
        }

        return (empty($array)) ? true : false;
    }

    /**
     * @param string $key
     * @param array|string|null $value
     *
     * @return bool
     */
    public function isIgnoredValue($key, $value, $defaultValue)
    {
        return ($value == '' || $value == null || empty($value)) ? true : false;
    }

    /**
     * @param string $key
     * @param array|string|null $value
     *
     * @return bool
     */
    public function isDefaultValue($key, $value, $defaultValue)
    {
        return $defaultValue ? $value == $defaultValue : false;
    }

}
