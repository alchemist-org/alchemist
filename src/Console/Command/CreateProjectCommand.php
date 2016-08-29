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
use Alchemist\Template;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alchemist\Manager;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class CreateProjectCommand extends Command
{

    /** @var Manager $manager */
    private $manager;

    /**
     * @param Manager $manager
     */
    public function __construct(
        Manager $manager
    )
    {
        parent::__construct();
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setName('create-project')
            ->setDescription('Create project')
            ->setDefinition(array(
                new InputArgument('name', InputArgument::REQUIRED, 'Project name'),
                new InputOption('template', 't', InputOption::VALUE_REQUIRED, 'Template name', Template::DEFAULT_TEMPLATE),
                new InputOption('projects-dir', 'd', InputOption::VALUE_REQUIRED, 'Projects dir'),
                new InputOption('type', null, InputOption::VALUE_REQUIRED, 'Type, e.g. git, composer..'),
                new InputOption('value', null, InputOption::VALUE_REQUIRED, 'Value, e.g. url, package-name..'),
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'Re-create alredy existing project'),
                new InputOption('save', 's', InputOption::VALUE_NONE, 'Save change to distant sources')
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->manager;

        $name = $input->getArgument('name');

        $templateName = $input->getOption('template');
        $force = $input->getOption('force');
        $save = $input->getOption('save');

        $options = array(
            'projects-dir' => $input->getOption('projects-dir'),
            'origin-source' => array(
                'type' => $input->getOption('type'),
                'value' => $input->getOption('value')
            )
        );

        $result = $manager->createProject($name, $templateName, $options, $save, $force);

        ConsoleUtils::writeln($result, $output);
    }

}
