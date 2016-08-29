<?php

namespace Alchemist\Console\Command;

use Alchemist\Console\Utils\ConsoleUtils;
use Alchemist\Manager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class DefaultCommand extends Command
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
            ->setName('default');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $manager = $this->manager;

        $result = $manager->touchProjects();

        ConsoleUtils::writeln($result, $output);
    }

}