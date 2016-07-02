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
            'projects-dir' => null,  #required ['projects location']
            'origin-source' => array(
                'types' => array(      #the most usage as defaults
                    'composer' => 'composer create-project <name> <project-dir>',
                    'git' => 'git clone <url> <project-dir>'
                ),
                'type' => null,    #optional ['means default origin-source type, required when is origin-source used']
                'url' => null,      #optional ['required when is used to parse inside type <url>']
                'name' => null      #optional ['required when is used to parse inside type <name>']
            )
        ),
        'core' => array(
            'template' => null,   #optional ['by default is template not set']
            'templates' => null   #optional ['templates folder is required when is needed']
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

}