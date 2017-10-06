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

    /** @var string */
    const BEFORE_CREATE = 'before_create';
    /** @var string */
    const BEFORE_CREATE_ROOT = 'before_create_root';
    /** @var string */
    const AFTER_CREATE = 'after_create';
    /** @var string */
    const AFTER_CREATE_ROOT = 'after_create_root';
    /** @var string */
    const BEFORE_REMOVE = 'before_remove';
    /** @var string */
    const BEFORE_REMOVE_ROOT = 'before_remove_root';
    /** @var string */
    const AFTER_REMOVE = 'after_remove';
    /** @var string */
    const AFTER_REMOVE_ROOT = 'before_remove_root';
    /** @var string */
    const REMOVE = 'remove';
    /** @var string */
    const CREATE = 'create';
    /** @var string */
    const SUPPRESS = 'suppress';
    /** @var string */
    const SAVE = 'save';
    /** @var string */
    const TOUCH = 'touch';
    /** @var string */
    const CREATE_ORIGIN_SOURCE = 'create_origin_source';
    /** @var Configurator */
    private $configurator;
    /** @var TemplateLoader */
    private $templateLoader;

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
     * @param bool $force
     * @param bool $suppress
     *
     * @return array
     */
    public function install($force = false, $suppress = false)
    {
        $result = [];

        // load all distant sources
        foreach ($this->configurator->getConfig()->getDistantSources() as $distantSourceName => $distantSource) {

            // [ CLEAR INSTALL ]
            foreach ($distantSource as $projectName => $projectData) {

                // load templateNames
                $templateNames = array();
                $templates = $this->loadTemplatePerProject($projectName);
                foreach ($templates as $template) {
                    $templateNames[] = $template->getName();
                }

                // load originSource
                $originSource = isset($projectData['origin-source']) ? $projectData['origin-source'] : [];

                // config distant source parameters
                $parameters = isset($projectData['parameters']) ? $projectData['parameters'] : [];

                // create project
                $result[] = $this->createProject(
                    $projectName,
                    $templateNames,
                    array_merge($parameters, ['origin-source' => $originSource]),
                    false,
                    $force,
                    $suppress
                );
            }
        }

        return $result;
    }

    /**
     * @param string $projectName
     * @param null $projectsDir
     * @param bool $isFirstAddedProjectInProjectsDir
     *
     * @return array
     */
    public function touchProject($projectName, $projectsDir = null, $isFirstAddedProjectInProjectsDir = false)
    {
        $results = [];

        // touch only specific directory
        if ($projectsDir) {
            if ($isFirstAddedProjectInProjectsDir) {
                $projectsDirName = $this->configurator->getConfig()->getProjectsDirName($projectsDir);
                $results[$projectsDir] = $this->runScript("echo $projectsDirName:");
            }
            $templates = $this->loadTemplatePerProject($projectName);
            $results[$projectsDir][$projectName] = $this->touchProjectForTemplates($projectName,
                $templates,
                $projectsDir);
        } else {
            // touch every directory which is possible
            foreach ($this->configurator->getConfig()->getProjectsDirsPaths() as $projectsDirName => $projectsDirPath) {
                $templates = $this->loadTemplatePerProject($projectName);
                $resultInCurrentProjectsDir = $this->touchProjectForTemplates($projectName,
                    $templates,
                    $projectsDirPath);
                if ($resultInCurrentProjectsDir) {
                    $results[$projectsDirName] = $this->runScript("echo $projectsDirName:");
                    $results[$projectsDirName][$projectName] = $resultInCurrentProjectsDir;
                }
            }
        }

        return $results;
    }

    /**
     * Returns associated template or new Template()
     *
     * @param string $projectName
     *
     * @return Template[]
     */
    public function loadTemplatePerProject($projectName)
    {
        $result = [];

        // load template
        $templateName = null;
        $distantSources = $this->configurator->getConfig()->getDistantSources();
        foreach ($distantSources as $distantSource) {
            foreach ($distantSource as $distantSourceProjectName => $projectData) {
                if ($distantSourceProjectName == $projectName) {
                    $templateName = isset($projectData['core']['template']) ? $projectData['core']['template'] : [];

                    // load default template
                    $templates = $templateName;

                    if (is_array($templates)) {
                        foreach ($templates as $template) {
                            $result[] = $this->templateLoader->getTemplate($template);
                        }
                    } else {
                        $result[] = $this->templateLoader->getTemplate($templates);
                    }

                    if (empty($result)) {
                        $projectsDirName = isset($projectData['parameters']['projects-dir']) ? $projectData['parameters']['projects-dir'] : null;
                        if ($projectsDirName) {
                            $projectsDirTemplate = $this->configurator->getConfig()
                                ->getProjectsDirTemplate($projectsDirName);
                            if ($projectsDirTemplate) {
                                $result[] = $this->templateLoader->getTemplate($projectsDirTemplate);
                            }
                        }
                    }

                    if (empty($result)) {
                        $templateDefaultName = $this->configurator->getConfig()->getTemplateName();
                        $result[] = $this->templateLoader->getTemplate($templateDefaultName);
                    }
                }
            }
        }

        // set default template
        if (is_array($result) && !count($result)) {
            $result = $this->templateLoader->getTemplate($this->configurator->getConfig()->getTemplateName());
        }

        return $result;
    }

    /**
     * @param string $projectName
     * @param Template|null $template
     * @param string $projectsDirPath
     *
     * @return string|null
     */
    public function touchProjectInternal($projectName, $template, $projectsDirPath)
    {
        $result = null;

        // template parameters merge to parameters loaded by config
        if ($template) {
            $this->configurator->getConfig()->applyParameters($template->getParameters());
        }

        // replacement parameters
        $replacementParameters = $this->configurator->getConfig()->getParameters();
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['projects-dir'] = $projectsDirPath;
        $replacementParameters['project-dir'] = $this->getProjectDir($projectName, $projectsDirPath);

        $projectDir = $this->getProjectDir($projectName, $projectsDirPath);
        if (is_readable($projectDir)) {
            $touchScript = $this->runScript($template->getScript(self::TOUCH), $replacementParameters);
            $result = $template ? $touchScript : null;
        }

        return $result;
    }

    /**
     * @param array|string|null $script
     * @param array $replaceParameters
     *
     * @return array
     */
    public function runScript($script, $replaceParameters = [])
    {
        // string -> array
        $script = is_string($script) ? [$script] : $script;

        $result = [];
        foreach ($script as $scriptLine) {
            // filter out what is not a string
            $replaceParametersFiltered = array_filter($replaceParameters,
                function($value) {
                    return is_string($value) || is_integer($value) ? $value : null;
                });

            if (is_array($scriptLine)) {
                foreach ($scriptLine as $line) {
                    $parsedLine = Parser::parse($line, $replaceParametersFiltered);
                    $result[] = Console::execute($parsedLine);
                }
            } else {
                $result[] = Console::execute(Parser::parse($scriptLine, $replaceParametersFiltered));
            }
        }
        return $result;
    }

    /**
     * @param string $projectName
     * @param string|null $templates
     * @param array $parameters
     * @param bool $save
     * @param bool $force
     * @param bool $suppress
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createProject(
        $projectName,
        $templates = Template::DEFAULT_TEMPLATE,
        array $parameters = [],
        $save = false,
        $force = false,
        $suppress = false
    ) {
        $results = [];

        $isFirst = true;
        if (is_array($templates) && count($templates)) {
            foreach ($templates as $key => $templateName) {
                $results[] = $this->createProjectInternal($projectName,
                    $templateName,
                    $parameters,
                    $save,
                    $force,
                    $isFirst,
                    $suppress);
                if ($isFirst) {
                    $isFirst = false;
                }
            }
        } else {
            $results[] = $this->createProjectInternal($projectName,
                $templates,
                $parameters,
                $save,
                $force,
                $isFirst,
                $suppress);
        }

        return $results;
    }

    /**
     * @param string $projectName
     * @param bool $save
     * @param array $parameters
     *
     * @throws \Exception
     *
     * @return array
     */
    public function removeProject($projectName, $save = false, array $parameters = [])
    {
        $result = [];

        // load templates
        $templates = $this->loadTemplatePerProject($projectName);

        $isFirst = true;
        if (is_array($templates) && count($templates)) {
            foreach ($templates as $key => $template) {
                $results[] = $this->removeProjectInternal($projectName, $template, $parameters, $save, $isFirst);
                if ($isFirst) {
                    $isFirst = false;
                }
            }
        } else {
            $results[] = $this->removeProjectInternal($projectName, null, $parameters, $save, $isFirst);
        }

        return $result;
    }

    /**
     * @return void
     */
    public function selfUpdate()
    {
        // git
        $this->runScript("git pull origin master");

        // purge temp dir
        \Tester\Helpers::purge(TEMP_DIR);
    }

    /**
     * @param string|null $filterProjectName
     *
     * @return array
     */
    public function touchProjects($filterProjectName = null)
    {
        $result = [];

        // load all projects
        $isFirstAddedProjectInProjectsDir = true;
        foreach ($this->configurator->getConfig()->getProjectsDirsPaths() as $projectsDirName => $projectsDirPath) {
            $mask = $projectsDirPath . DIRECTORY_SEPARATOR . '*';
            $projects = glob($mask, GLOB_ONLYDIR);

            if (!empty($projects)) {
                foreach ($projects as $projectDir) {
                    $projectName = basename($projectDir);

                    if ($filterProjectName) {
                        if ($projectName == $filterProjectName) {
                            $result[$projectsDirName][$projectName] = $this->touchProject($projectName,
                                $projectsDirPath,
                                $isFirstAddedProjectInProjectsDir);
                            if ($result[$projectsDirName][$projectName]) {
                                $isFirstAddedProjectInProjectsDir = false;
                            }
                        }
                    } else {
                        $result[$projectsDirName][$projectName] = $this->touchProject($projectName,
                            $projectsDirPath,
                            $isFirstAddedProjectInProjectsDir);
                        if ($result[$projectsDirName][$projectName]) {
                            $isFirstAddedProjectInProjectsDir = false;
                        }
                    }
                }
            }
            $isFirstAddedProjectInProjectsDir = true;
        }

        return $result;
    }

    /**
     * @param null|string $templateName
     * @param null|string $projectDir
     * @param null|string $projectsDirName
     *
     * @return array
     */
    public function save($templateName = null, $projectsDirName = null, $projectDir = null)
    {
        $result = [];

        if ($projectDir) {
            $projectName = basename($projectDir);

            if(is_dir("$projectDir/.git")) {
                $remoteOriginUrl = exec("cd $projectDir && git config --get remote.origin.url");
            } else {
                $remoteOriginUrl = null;
            }

            // add git project
            $config = $this->configurator->getConfig();

            $email = exec("cd $projectDir && git config user.email");
            $name = exec("cd $projectDir && git config user.name");

            // origin source
            $distantSourceData = $config->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
            $distantSourceData[$projectName] = [
                'parameters' => [
                    'projects-dir' => $projectsDirName
                ],
                'core' => [
                    'template' => $this->configurator->getConfig()
                        ->getProjectsDirTemplate($projectsDirName) != $templateName ? $templateName : null
                ],
                'origin-source' => [
                    'type' => $remoteOriginUrl ? 'git' : null,
                    'value' => $remoteOriginUrl ? $remoteOriginUrl : null,
                    'email' => $remoteOriginUrl ? $email : null,
                    'name' => $remoteOriginUrl ? $name : null
                ]
            ];
            $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, $distantSourceData);

            // load template
            $templates = $templateName ? [$this->templateLoader->getTemplate($templateName)] : $this->loadTemplatePerProject($projectName);

            // template parameters merge to parameters loaded by config
            if ($templates) {
                $this->configurator->getConfig()->applyParameters($templates[0]->getParameters());
            }

            // replacement parameters
            $replacementParameters = $this->configurator->getConfig()->getParameters();
            $replacementParameters['project-name'] = $projectName;
            $replacementParameters['project-dir'] = $projectDir;

            $result[] = $templates ? $this->runScript($templates[0]->getScript(self::SAVE),
                $replacementParameters) : null;

            $this->configurator->setConfig($config);
        } else {
            // load all projects
            foreach ($this->configurator->getConfig()->getProjectsDirs() as $projectsDirName => $projectsDirData) {
                $projectsDirTemplate = null;
                if (is_array($projectsDirData)) {
                    $projectsDirPath = $projectsDirData['path'];
                    $projectsDirTemplate = isset($projectsDirData['core']['template']) ? $projectsDirData['core']['template'] : null;
                } else {
                    $projectsDirPath = $projectsDirData;
                }

                $mask = $projectsDirPath . DIRECTORY_SEPARATOR . '*';
                $projects = glob($mask, GLOB_ONLYDIR);

                foreach ($projects as $projectDir) {
                    $projectName = basename($projectDir);

                    $remoteOriginUrl = exec("cd $projectDir && git config --get remote.origin.url");

                    // add git project
                    if ($remoteOriginUrl) {
                        $config = $this->configurator->getConfig();

                        $email = exec("cd $projectDir && git config user.email");
                        $name = exec("cd $projectDir && git config user.name");

                        // origin source
                        $distantSourceData = $config->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
                        $distantSourceData[$projectName] = [
                            'parameters' => [
                                'projects-dir' => $projectsDirName
                            ],
                            'core' => [
                                'template' => $projectsDirTemplate
                            ],
                            'origin-source' => [
                                'type' => 'git',
                                'value' => $remoteOriginUrl,
                                'email' => $email,
                                'name' => $name
                            ]
                        ];
                        $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, $distantSourceData);

                        // load template
                        $templates = $this->loadTemplatePerProject($projectName);

                        // template parameters merge to parameters loaded by config
                        if ($templates) {
                            $this->configurator->getConfig()->applyParameters($templates[0]->getParameters());
                        }

                        // replacement parameters
                        $replacementParameters = $this->configurator->getConfig()->getParameters();
                        $replacementParameters['project-name'] = $projectName;
                        $replacementParameters['project-dir'] = $projectDir;

                        $result[] = $templates ? $this->runScript($templates[0]->getScript(self::SAVE),
                            $replacementParameters) : null;

                        $this->configurator->setConfig($config);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    function which()
    {
        $path = realpath(dirname(__FILE__));
        return $this->runScript("cd $path && git rev-parse --show-toplevel");
    }

    /**
     * @param string $projectName
     * @param []Template|null|string $templates
     * @param string $projectsDirPath
     *
     * @return array
     */
    private function touchProjectForTemplates($projectName, $templates, $projectsDirPath)
    {
        $results = [];

        // many templates => for each template
        if (is_array($templates) && count($templates)) {
            foreach ($templates as $key => $template) {
                $result = $this->touchProjectInternal($projectName, $template, $projectsDirPath);
                if ($result) {
                    $results[] = $result;
                }
            }
        } else {
            // no templates = null
            if (is_array($templates)) {
                $result = $this->touchProjectInternal($projectName, null, $projectsDirPath);
                if ($result) {
                    $results[] = $result;
                }
            } else {
                // one template
                $result = $this->touchProjectInternal($projectName, $templates, $projectsDirPath);
                if ($result) {
                    $results[] = $result;
                }
            }
        }

        return $results;
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
     * @param bool $isFirstCreating
     * @param bool $suppress
     *
     * @return array
     *
     * @throws \Exception
     */
    private function createProjectInternal(
        $projectName,
        $templateName,
        array $parameters = [],
        $save = false,
        $force = false,
        $isFirstCreating = true,
        $suppress = false
    ) {
        $result = [];

        // get config force because apply parameters are there from previous createProjectInternal
        $this->configurator->getConfig()->setUpCachedParameters();

        // use default template
        if ($templateName == Template::DEFAULT_TEMPLATE) {
            $templateName = $this->configurator->getConfig()->getTemplateName();
        }

        // load template
        $template = $templateName ? $this->templateLoader->getTemplate($templateName) : null;

        // template & console parameters merge to parameters loaded by config
        if ($template) {
            $this->configurator->getConfig()->applyParameters($template->getParameters());
        }
        if ($parameters) {
            $this->configurator->getConfig()->applyParameters($parameters);
        }

        // load projectDir
        $projectDir = $this->getProjectDir($projectName, $this->configurator->getConfig()->getProjectsDir());

        // replacement parameters
        $replacementParameters = $this->configurator->getConfig()->getParameters();
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            // duplicates are not allowed, if is force enable, remove project
            if (file_exists($projectDir)) {
                if ($force) {
                    $this->removeProject($projectName);
                } else {
                    if (!$suppress) {
                        throw new \Exception("Project '$projectName' ['$projectDir'] already exists.");
                    } else {
                        $result[self::SUPPRESS] = $template ? $this->runScript($template->getScript(self::SUPPRESS),
                            $replacementParameters) : [];
                        return $result;
                    }
                }
            }
        }

        // run before_create
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::BEFORE_CREATE_ROOT] = isset($this->configurator->getConfig()
                    ->getConfig()[self::BEFORE_CREATE]) ? $this->runScript($this->configurator->getConfig()
                ->getConfig()[self::BEFORE_CREATE],
                $replacementParameters) : [];
        }
        $result[self::BEFORE_CREATE] = $template ? $this->runScript($template->getScript(self::BEFORE_CREATE),
            $replacementParameters) : [];

        // create project (actually)
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::CREATE] = Console::execute("mkdir $projectDir");
        }

        // load origin source
        $originSource = $template && isset($template->getParameters()['origin-source']) ? $template->getParameter('origin-source') : $this->configurator->getConfig()
            ->getParameter('origin-source');
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

            $result[self::CREATE_ORIGIN_SOURCE] = $this->runScript($sourceTypes[$originSourceType],
                $replacementParametersOriginSource);
        } else {
            $result[self::CREATE_ORIGIN_SOURCE] = [];
        }

        // run after create
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::AFTER_CREATE_ROOT] = isset($this->configurator->getConfig()
                    ->getConfig()[self::AFTER_CREATE]) ? $this->runScript($this->configurator->getConfig()
                ->getConfig()[self::AFTER_CREATE],
                $replacementParameters) : [];
        }
        $result[self::AFTER_CREATE] = $template ? $this->runScript($template->getScript(self::AFTER_CREATE),
            $replacementParameters) : [];

        // get config force because apply parameters are there from previous createProjectInternal
        $this->configurator->getConfig()->setUpCachedParameters();

        // save
        if ($save) {
            $projectsDirNameOrPath = isset($parameters['projects-dir']) ? $parameters['projects-dir'] : null;
            if (is_dir($projectsDirNameOrPath)) {
                $projectsDirNameOrPath = $this->configurator->getConfig()->getProjectsDirName($projectsDirNameOrPath);
            }
            $this->save($templateName, $projectsDirNameOrPath, $projectDir);
        }

        return $result;
    }

    /**
     * @param string $projectName
     * @param Template|null $template
     * @param array $parameters
     * @param bool $save
     * @param bool $isFirstCreating
     *
     * @throws \Exception
     *
     * @return array
     */
    private function removeProjectInternal(
        $projectName,
        $template,
        array $parameters = [],
        $save = false,
        $isFirstCreating = true
    ) {
        $results = [];

        // template & console parameters merge to parameters loaded by config
        if ($template) {
            $this->configurator->getConfig()->applyParameters($template->getParameters());
        }
        if ($parameters) {
            $this->configurator->getConfig()->applyParameters($parameters);
        }

        $projectsDir = $this->configurator->getConfig()->getProjectsDir();
        $projectDir = $this->getProjectDir($projectName, $projectsDir);

        // check if project exists
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            if (!is_readable($projectDir)) {
                throw new \Exception("Project '$projectName' can not be removed.");
            }
        }

        // replacement parameters
        $replacementParameters = $this->configurator->getConfig()->getParameters();
        $replacementParameters['project-name'] = $projectName;
        $replacementParameters['project-dir'] = $projectDir;

        // run before remove
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::BEFORE_REMOVE_ROOT] = isset($this->configurator->getConfig()
                    ->getConfig()[self::BEFORE_REMOVE]) ? $this->runScript($this->configurator->getConfig()
                ->getConfig()[self::BEFORE_REMOVE],
                $replacementParameters) : [];
        }
        $result[self::BEFORE_REMOVE] = $template ? $this->runScript($template->getScript(self::BEFORE_REMOVE),
            $replacementParameters) : [];

        // remove project (actually)
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::REMOVE] = Console::execute("sudo rm -rf $projectDir");
        }

        // run after remove
        // used when is called more templates in row, only first create projectDir
        if ($isFirstCreating) {
            $result[self::AFTER_REMOVE_ROOT] = isset($this->configurator->getConfig()
                    ->getConfig()[self::AFTER_REMOVE]) ? $this->runScript($this->configurator->getConfig()
                ->getConfig()[self::AFTER_REMOVE],
                $replacementParameters) : [];
        }
        $result[self::AFTER_REMOVE] = $template ? $this->runScript($template->getScript(self::AFTER_REMOVE),
            $replacementParameters) : [];

        // remove from distant source
        if ($save) {
            $config = $this->configurator->getConfig();

            $distantSources = [];
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

        return $results;
    }
}