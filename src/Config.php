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

    public function getSourceTypes()
    {
        return $this->config['core']['source-types'];
    }

    public function getSourceType($name)
    {
        return $this->config['core']['source-types'][$name];
    }

    public function getProjectsDir()
    {
        return $this->config['parameters']['projects-dir'];
    }

    public function getParameters()
    {
        return $this->config['parameters'];
    }

    public function applyConsoleParameters(array $parameters = array())
    {
        return $this->config['parameters'] = Arrays::merge($this->config['parameters'], $parameters);
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

}