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
use Nette\Neon\Neon;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class TemplateLoader
{

    /** @var Configurator */
    private $configurator;

    /**
     * TemplateLoader constructor.
     *
     * @param Configurator $configurator
     */
    public function __construct(Configurator $configurator)
    {
        $this->configurator = $configurator;
    }

    /**
     * @param string $templateName
     *
     * @return Template
     *
     * @throws \Exception
     */
    public function getTemplate($templateName)
    {
        // load config
        $config = $this->configurator->getConfig();

        // load template
        $template = $this->loadTemplate($templateName, $config->getTemplatesDir());

        return $template;
    }

    /**
     * @param string $templateName
     * @param string $templatesDir
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function loadTemplate($templateName, $templatesDir)
    {
        $path = $this->getTemplatePath($templateName, $templatesDir);

        if (!file_exists($path)) {
            throw new \Exception("Template '$path' does not exist");
        }

        $contents = file_get_contents($path);
        $data = Neon::decode($contents) ? Neon::decode($contents) : array();
        $template = new Template($templateName, $data);

        return $template;
    }

    /**
     * @param string $templateName
     * @param string $templatesDir
     *
     * @return string
     */
    private function getTemplatePath($templateName, $templatesDir)
    {
        return $templatesDir . DIRECTORY_SEPARATOR . $templateName . '.neon';
    }

}
