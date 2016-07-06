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
class Manager
{

    /** @var Configurator */
    private $configurator;

    /** @var TemplateLoader */
    private $templateLoader;

    /** @var string */
    const BEFORE_CREATE = 'before_create';

    /** @var string */
    const AFTER_CREATE = 'after_create';

    /** @var string */
    const BEFORE_REMOVE = 'before_remove';

    /** @var string */
    const AFTER_REMOVE = 'after_remove';

    /** @var string */
    const REMOVE = 'remove';

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
    public function __construct(Configurator $configurator, TemplateLoader $templateLoader)
    {
        $this->configurator = $configurator;
        $this->templateLoader = $templateLoader;
    }

    /**
     * @param string $projectName
     * @param bool $save
     *
     * @return array
     */
    public function removeProject($projectName, $save = true)
    {
        $result = [];

        $projectsDir = $this->configurator->getConfig()->getProjectsDir();
        $projectDir = $this->getProjectDir($projectName, $projectsDir);

        // check if project exists
        if (!is_readable($projectDir)) {
            throw new \Exception("Project $projectName can not be removed.");
        }

        // load template
        $templateName = null;
        foreach ($this->configurator->getConfig()->getDistantSources() as $distantSource) {
            foreach ($distantSource as $distantSourceProjectName => $projectData) {
                if ($distantSourceProjectName == $projectName) {
                    $templateName = isset($projectData['template']) ? $projectData['template'] : null;
                }
            }
        }

        // load template
        $template = $templateName ? $this->templateLoader->getTemplate($templateName) : null;

        // replacement parameters
        $replacementParameters = $template ? $template->getParameters() : $this->configurator->getConfig()->getParameters();

        // add common replacement parameters
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // run before remove
        $result[self::BEFORE_REMOVE] = $template ? $this->runScript($template->getScript(self::BEFORE_REMOVE), $replacementParameters) : [];

        // remove project (actually)
        $result[self::REMOVE] = Console::execute("rm -rf $projectDir");

        // run after remove
        $result[self::AFTER_REMOVE] = $template ? $this->runScript($template->getScript(self::AFTER_REMOVE), $replacementParameters) : [];

        // remove from distant source
        if ($save) {
            $config = $this->configurator->getConfig();

            foreach ($this->configurator->getConfig()->getDistantSources() as $distantSourceName => $distantSourceData) {
                foreach ($distantSourceData as $distantSourceProjectName => $projectData) {
                    if ($projectName == $distantSourceProjectName) {
                        unset($distantSourceData[$projectName]);
                        $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, array(
                                $distantSourceData
                            )
                        );
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @param string $projectName
     * @param string $projectsDir
     *
     * @return string
     */
    private function getProjectDir($projectName, $projectsDir)
    {
        return $projectsDir . DIRECTORY_SEPARATOR . $projectName;
    }

    /**
     * @param string $projectName
     * @param string|Template::DEFAULT_TEMPLATE|null $templateName
     * @param array $parameters
     * @param bool $save
     * @param bool $force
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createProject(
        $projectName,
        $templateName = Template::DEFAULT_TEMPLATE,
        array $parameters = array(),
        $save = false,
        $force = false
    )
    {
        $result = [];

        // use default template
        if ($templateName == Template::DEFAULT_TEMPLATE) {
            $templateName = $this->configurator->getConfig()->getTemplateName();
        }

        // merge config parameters with console parameters
        $parameters = Arrays::merge($this->configurator->getConfig()->getParameters(), $parameters);

        // load template
        $template = $templateName ? $this->templateLoader->getTemplate($templateName, $parameters) : null;

        // load projectDir
        $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

        // duplicates are not allowed, if is force enable, remove project
        if (file_exists($projectDir)) {
            if ($force) {
                $this->removeProject($projectName);
            } else {
                throw new \Exception("Project '$projectName' ['$projectDir'] already exists.");
            }
        }

        // replacement parameters
        $replacementParameters = $template ? $template->getParameters() : $this->configurator->getConfig()->applyConsoleParameters($parameters);

        // add common replacement parameters
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // run before_create
        $result[self::BEFORE_CREATE] = $template ? $this->runScript($template->getScript(self::BEFORE_CREATE), $replacementParameters) : [];

        // create project (actually)
        $result[self::CREATE] = Console::execute("mkdir $projectDir");

        // load origin source
        $originSource = $template && isset($template->getParameters()['origin-source']) ? $template->getParameter('origin-source') : $this->configurator->getConfig()->getParameter('origin-source');
        $sourceTypes = $this->configurator->getConfig()->getSourceTypes();

        // load project from origin source (actually)
        $originSourceType = $originSource['type'];
        if ($originSourceType) {
            if (!isset($sourceTypes[$originSourceType])) {
                throw new \Exception("Source type '$originSourceType' does not exist.");
            }

            // add specific replacement parameters
            $originSourceParameters = $originSource;
            unset($originSourceParameters['types']);
            $replacementParametersOriginSource = Arrays::merge($replacementParameters, $originSourceParameters);

            $result[self::CREATE_ORIGIN_SOURCE] = $this->runScript($sourceTypes[$originSourceType], $replacementParametersOriginSource);
        } else {
            $result[self::CREATE_ORIGIN_SOURCE] = [];
        }

        // run after create
        $result[self::AFTER_CREATE] = $template ? $this->runScript($template->getScript(self::AFTER_CREATE), $replacementParameters) : [];

        // save project to default distant-source
        if ($save) {
            $config = $this->configurator->getConfig();

            $distantSourceData = $config->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
            $distantSourceData[$projectName] = array(
                'origin-source' => array(
                    'type' => $originSourceType,
                    'value' => $originSource['value'],
                )
            );
            $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, $distantSourceData);

            $this->configurator->setConfig($config);
        }

        return $result;
    }

    /**
     * @param array|string|null $script
     * @param array $replaceParameters
     *
     * @return array
     */
    public function runScript($script, $replaceParameters = array())
    {
        // string -> array
        $script = is_string($script) ? array($script) : $script;

        $result = [];
        foreach ($script as $scriptLine) {
            // filter out what is not a string
            $replaceParametersFiltered = array_filter($replaceParameters, function ($value) {
                return is_string($value) ? $value : null;
            });
            $scriptLine = Parser::parse($scriptLine, $replaceParametersFiltered);
            $result[] = Console::execute($scriptLine);
        }
        return $result;
    }

    /**
     * @param string $name
     * @param array $data
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function setDistantSource($name, $data)
    {
        return $this->configurator->getConfig()->setDistantSource($name, $data);
    }

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function install()
    {
        $result = [];

        // load all distant sources
        foreach ($this->configurator->getConfig()->getDistantSources() as $distantSourceName => $distantSource) {

            // [ CLEAR INSTALL ]
            foreach ($distantSource as $projectName => $projectData) {

                // load templateName
                $templateName = isset($projectData['core']['template']) ? $projectData['core']['template'] : null;

                // load originSource
                $originSource = isset($projectData['origin-source']) ? $projectData['origin-source'] : array();

                // create project
                $result = $this->createProject($projectName, $templateName, array(
                    'origin-source' => $originSource
                ),
                    false,
                    true
                );

                // add result
                $result[$distantSourceName] = $result;
            }
        }

        return $result;
    }

    /**
     * @param string $projectName
     *
     * @return string
     */
    public function touchProject($projectName)
    {
        $projectsDir = $this->configurator->getConfig()->getProjectsDir();
        $projectDir = $this->getProjectDir($projectName, $projectsDir);
        return !is_readable($projectDir) ? "" : $projectDir;
    }

    /**
     * @return void
     */
    public function selfUpdate()
    {
        $this->runScript("git pull origin master");

        //TODO: purge cached generated container
    }

}
