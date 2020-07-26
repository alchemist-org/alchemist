<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist\Console\Command;

use Alchemist\Console\Utils\ConsoleUtils;
use Alchemist\DistantSource\GithubSourceManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alchemist\Template;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class LoadGithubSourcesCommand extends Command
{

    /** @var GithubSourceManager $manager */
    private $githubSourceManager;

    /**
     * @param GithubSourceManager $manager
     */
    public function __construct(
        GithubSourceManager $githubSourceManager
    )
    {
        parent::__construct();
        $this->githubSourceManager = $githubSourceManager;
    }

    protected function configure()
    {
        $this
            ->setName('load-github-sources')
            ->setDescription('Load github sources')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'Github username'),
                new InputOption('template', 't', InputOption::VALUE_REQUIRED, 'Template name'),
                new InputOption('projects-dir', 'd', InputOption::VALUE_REQUIRED, 'Projects dir'),
                new InputOption('install', 'i', InputOption::VALUE_NONE, 'Install projects'),
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'Re-create alredy existing project'),
                new InputOption('suppress', 's', InputOption::VALUE_NONE, 'Suppress re-create already existing projects')
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->githubSourceManager;

        $username = $input->getArgument('username');
        $template = $input->getOption('template');
        $projectsDir = $input->getOption('projects-dir');
        $install = $input->getOption('install');
        $force = $input->getOption('force');
        $suppress = $input->getOption('suppress');

        if($force && $suppress) {
            throw new \InvalidArgumentException("Option -f [force] & -s [suppress] can not be set up together.");
        }

        $result = $manager->loadGithubSources($username, $projectsDir, $template, $install, $force, $suppress);

        ConsoleUtils::writeln($result, $output);

        return 0;
    }

}
