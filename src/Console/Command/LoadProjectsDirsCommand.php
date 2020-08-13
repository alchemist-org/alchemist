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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Command\Command;
use Alchemist\Manager;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class LoadProjectsDirsCommand extends Command
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
            ->setName('load-projects-dirs')
            ->setDescription('Load projects dirs')
            ->setDefinition(array(
                new InputOption('name', 'name', InputOption::VALUE_REQUIRED, 'Projects dir name'),
                new InputOption('path', 'path', InputOption::VALUE_REQUIRED, 'Projects dir path'),
                new InputOption('template', 'template', InputOption::VALUE_REQUIRED, 'Projects dir template name(s)'),
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->manager;

        $name = $input->getOption('name');
        $path = $input->getOption('path');
        $template = $input->getOption('template');
 
        $result = $manager->loadProjectsDirs($name, $path, $template);

        ConsoleUtils::writeln($result, $output);

        return 0;
    }

}
