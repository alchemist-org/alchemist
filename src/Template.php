<?php

namespace Alchemist;

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Alchemist\Utils\Arrays;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Template
{

    /** @var string */
    private $name;

    /** @var array */
    private $data;

    /** @var array */
    private $default = array(
        'parameters' => array(),
        'touch' => array(
            "echo '<project-name>': '<project-dir>'"
        ),
        'before_remove' => array(),
        'after_remove' => array(),
        'before_create' => array(),
        'after_create' => array()
    );

    /** @var string */
    const DEFAULT_TEMPLATE = 'default';

    /**
     * Template constructor.
     *
     * @param null|string $name
     * @param null|array $data
     */
    public function __construct($name = null, $data = null)
    {
        $this->name = $name;
        $this->data = $data ? Arrays::merge($this->default, $data) : $this->default;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        return $this->data['parameters'][$name];
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getScript($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : array();
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->data['parameters'];
    }

    /**
     * @param array $parameters
     *
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->data['parameters'] = $parameters;
    }

}
