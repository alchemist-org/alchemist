<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist\DistantSource;

use Alchemist\Manager;
use Alchemist\Configurator;
use Alchemist\DistantSource;
use Alchemist\TemplateLoader;
use Github\Client;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class GithubSourceManager
{

    /** @var Manager */
    private $manager;

    /** @var Configurator */
    private $configurator;

    /** @var TemplateLoader */
    private $templateLoader;

    /**
     * GithubSourceManager constructor.
     *
     * @param Manager $manager
     * @param Configurator $configurator
     * @param TemplateLoader $templateLoader
     */
    public function __construct(Manager $manager, Configurator $configurator, TemplateLoader $templateLoader)
    {
        $this->manager = $manager;
        $this->configurator = $configurator;
        $this->templateLoader = $templateLoader;
    }

    public function loadGithubSources($githubUsername, $projectsDirName, $templateName, $install, $force, $suppress) 
    {
        $result = [];

        $client = new Client();
        $repos = $client->api('user')->repositories($githubUsername);

        // add distant sources
        foreach($repos as $repo) {
            $projectName = $repo['name'];
            $projectUrl = $repo['ssh_url'];

            // TODO: duplicated code (args to func pbbly project name, ssh_url, email, name)

            $config = $this->configurator->getConfig();

            // load template
            $templates = $templateName ? [$this->templateLoader->getTemplate($templateName)] : $this->manager->loadTemplatePerProject($projectName);

            // template parameters merge to parameters loaded by config
            if ($templates) {
                $this->configurator->getConfig()->applyParameters($templates[0]->getParameters());
            }

            // replacement parameters
            $replacementParameters = $this->configurator->getConfig()->getParameters();
            $replacementParameters['project-name'] = $projectName;
            $replacementParameters['project-dir'] = $projectsDirName;

            $email = $replacementParameters['name'];
            $name = $replacementParameters['email'];

            // add distant source
            $distantSourceData = $config->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
            $distantSourceData[$projectName] = [
                'parameters' => [
                     'projects-dir' => $projectsDirName
                ],
                'core' => [
                    'template' => $templateName
                ],
                'origin-source' => [
                    'type' => 'git',
                    'value' => $projectUrl,
                    'email' => $email,
                    'name' => $name
                ]
            ];
            $config->setDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE, $distantSourceData);

            $result[] = $templates ? $this->manager->runScript($templates[0]->getScript($this->manager::SAVE),
                $replacementParameters) : null;

            $this->configurator->setConfig($config);
        }

        // install
        if($install)
            $this->manager->install($force, $suppress);

        return $result;
    }
}
