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
    const TOUCH = 'touch';

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
     * @throws \Exception
     *
     * @return array
     */
    public function removeProject($projectName, $save = false)
    {
        $result = array();

        // load template
        $template = $this->loadTemplatePerProject($projectName);

        // template & console parameters merge to parameters loaded by config
        if($template) {
            $this->configurator->getConfig()->applyParameters($template->getParameters());
        }

        $projectsDir = $this->configurator->getConfig()->getProjectsDir();
        $projectDir = $this->getProjectDir($projectName, $projectsDir);

        // check if project exists
        if (!is_readable($projectDir)) {
            throw new \Exception("Project '$projectName' can not be removed.");
        }

        // replacement parameters
        $replacementParameters = $this->configurator->getConfig()->getParameters();
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // run before remove
        $result[self::BEFORE_REMOVE] = $template ? $this->runScript($template->getScript(self::BEFORE_REMOVE), $replacementParameters) : array();

        // remove project (actually)
        $result[self::REMOVE] = Console::execute("rm -rf $projectDir");

        // run after remove
        $result[self::AFTER_REMOVE] = $template ? $this->runScript($template->getScript(self::AFTER_REMOVE), $replacementParameters) : array();

        // remove from distant source
        if ($save) {
            $config = $this->configurator->getConfig();

            $distantSources= array();
            foreach ($config->getDistantSources() as $distantSourceName => $distantSourceData) {
                foreach ($distantSourceData as $distantSourceProjectName => $projectData) {
                    if ($projectName != $distantSourceProjectName) {
                        $distantSources[$distantSourceProjectName] = $projectData;
                    }
                }
            }
            $config->setDistantSources(
                $distantSources
            );

            $this->configurator->setConfig($config);
        }

        return $result;
    }

    /**
     * Returns associated template or new Template()
     *
     * @param string $projectName
     *
     * @return Template|null
     */
    public function loadTemplatePerProject($projectName)
    {
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
        return $templateName ? $this->templateLoader->getTemplate($templateName) : new Template;
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
        $result = array();

        // use default template
        if ($templateName == Template::DEFAULT_TEMPLATE) {
            $templateName = $this->configurator->getConfig()->getTemplateName();
        }

        // load template
        $template = $templateName ? $this->templateLoader->getTemplate($templateName) : null;

        // template & console parameters merge to parameters loaded by config
        if($template) {
            $this->configurator->getConfig()->applyParameters($template->getParameters());
        }
        if($parameters) {
            $this->configurator->getConfig()->applyParameters($parameters);
        }

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
        $replacementParameters = $this->configurator->getConfig()->getParameters();
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // run before_create
        $result[self::BEFORE_CREATE] = $template ? $this->runScript($template->getScript(self::BEFORE_CREATE), $replacementParameters) : array();

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
            $result[self::CREATE_ORIGIN_SOURCE] = array();
        }

        // run after create
        $result[self::AFTER_CREATE] = $template ? $this->runScript($template->getScript(self::AFTER_CREATE), $replacementParameters) : array();

        // save
        if ($save) {
            $config = $this->configurator->getConfig();

            // origin source
            $distantSourceData = $config->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
            $distantSourceData[$projectName] = array(
                'origin-source' => array(
                    'type' => $originSourceType,
                    'value' => $originSource['value']
                ),
                'template' => $templateName
            );
            $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, $distantSourceData);

            // projects-dir
            if (!isset(array_values($config->getProjectsDirs())[$projectDir])) {
                $projectsDirs = $config->getProjectsDirs();

                $projectsDirPath = $this->configurator->getConfig()->getProjectsDir();
                $projectsDirs[basename($projectsDirPath)] = $projectsDirPath;
                $config->setProjectsDirs($projectsDirs);
            }

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
                return is_string($value) || is_integer($value) ? $value : null;
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
     */
    public function setDistantSource($name, $data)
    {
        return $this->configurator->getConfig()->setDistantSource($name, $data);
    }

    /**
     * @return array
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
     * @param string $projectsDir
     *
     * @return string|null
     */
    public function touchProject($projectName, $projectsDir = null)
    {
        $result = null;

        $projectsDirs = $this->configurator->getConfig()->getProjectsDirs();
        foreach($projectsDirs as $projectsDirName => $projectsDirPath) {

            // load templateName
            $template = $this->loadTemplatePerProject($projectName);

            // template parameters merge to parameters loaded by config
            $this->configurator->getConfig()->applyParameters($template->getParameters());

            // replacement parameters
            $replacementParameters = $this->configurator->getConfig()->getParameters();
            $replacementParameters['project-name'] = $projectName;
            $replacementParameters['projects-dir'] = $projectsDirPath;
            $replacementParameters['project-dir'] = $projectsDirPath . DIRECTORY_SEPARATOR . $projectName;

            if($projectsDir) {
                if($projectsDir == $projectsDirPath) {
                    $projectDir = $this->getProjectDir($projectName, $projectsDir);
                    if (is_readable($projectDir)) {
                        $result = $this->runScript($template->getScript(self::TOUCH), $replacementParameters);
                    }
                }
            } else {
                $projectDir = $this->getProjectDir($projectName, $projectsDirPath);
                if (is_readable($projectDir)) {
                    $result = $this->runScript($template->getScript(self::TOUCH), $replacementParameters);
                }
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    public function selfUpdate()
    {
        // update
        $this->runScript("git pull origin master");

        // purge temp dir
        \Tester\Helpers::purge(TEMP_DIR);
    }

    /**
     * @return array
     */
    public function touchProjects($filterProjectName = null)
    {
        $result = array();

        // load all projects
        foreach ($this->configurator->getConfig()->getProjectsDirs() as $projectsDir) {
            $mask = $projectsDir . DIRECTORY_SEPARATOR . '*';
            $projects = glob($mask, GLOB_ONLYDIR);

            // run
            $projectsDirName = $this->configurator->getConfig()->getProjectsDirName($projectsDir);
            $result[$projectsDirName] = $this->runScript("echo $projectsDirName:");

            foreach ($projects as $projectDir) {
                $projectName = basename($projectDir);

                if($filterProjectName) {
                    if($projectName == $filterProjectName) {
                        $result[$projectsDirName][$projectName] = $this->touchProject($projectName, $projectsDir);
                    }
                } else {
                    $result[$projectsDirName][$projectName] = $this->touchProject($projectName, $projectsDir);
                }
            }
        }

        return $result;
    }

}
