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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alchemist\Manager;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class InstallCommand extends Command
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
            ->setName('install')
            ->setDescription('Install projects')
            ->setDefinition(array(
                new InputOption('force', 'f', InputOption::VALUE_NONE, 'Re-create already existing projects'),
                new InputOption('suppress', 's', InputOption::VALUE_NONE, 'Suppress re-create already existing projects')
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->manager;

        $force = $input->getOption('force');
        $suppress = $input->getOption('suppress');

        if($force && $suppress) {
            throw new \InvalidArgumentException("Option -f [force] & -s [suppress] can not be set up together.");
        }

        $result = $manager->install($force, $suppress);

        ConsoleUtils::writeln($result, $output);

        return 0;
    }

}
