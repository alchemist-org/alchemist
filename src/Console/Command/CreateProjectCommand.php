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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alchemist\Manager;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class CreateProjectCommand extends Command {

  /** @var Manager $manager */
  private $manager;
  
  /**
   * @param null|string $name
   * @param Manager $manager
   */
  public function __construct(
    $name = null,
    Manager $manager
  ) {
    parent::__construct($name);
    $this->manager = $manager;
  }

  protected function configure() {
    $this
      ->setName('create-project')
      ->setAliases(array(
        'create'
      ))
      ->setDescription('Create project')
      ->setDefinition(array(
        new InputArgument('name', InputArgument::REQUIRED, 'Project name'),
        new InputOption('template', 't', InputOption::VALUE_REQUIRED, 'Template'),
        new InputOption('projects-dir', 'd', InputOption::VALUE_REQUIRED, 'Projects dir'),
        new InputOption('origin-source.name', 'name', InputOption::VALUE_REQUIRED, 'Origin source name used in default type composer as package name'),
        new InputOption('origin-source.type', 'type', InputOption::VALUE_REQUIRED, 'Origin source type'),
        new InputOption('origin-source.url', 'url', InputOption::VALUE_REQUIRED, 'Origin source url used in default type git')
      ));
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $manager = $this->manager;

    $name = $input->getArgument('name');
    $template = $input->getOption('template');

    $options = array(
        'projects-dir' => $input->getOption('projects-dir'),
        'origin-source' => array(
            'name' => $input->getOption('origin-source.name'),
            'type' => $input->getOption('origin-source.type'),
            'url' => $input->getOption('origin-source.url'),
        )
    );

    $manager->createProject($name, $template, $options);
  }

}
