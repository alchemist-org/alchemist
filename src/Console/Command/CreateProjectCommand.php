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
        new InputArgument('name', InputArgument::REQUIRED, 'project name'),
        new InputOption('template', null, InputOption::VALUE_REQUIRED, 'template'),
        new InputOption('projects-dir', null, InputOption::VALUE_REQUIRED, 'projects-dir'),
        new InputOption('original-source.name', null, InputOption::VALUE_REQUIRED, 'original-source.name'),
        new InputOption('original-source.type', null, InputOption::VALUE_REQUIRED, 'original-source.type'),
        new InputOption('original-source.url', null, InputOption::VALUE_REQUIRED, 'original-source.url')
      ));
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $manager = $this->manager;

    $name = $input->getArgument('name');
    $template = $input->getOption('template');

    $options = array(
        'projects-dir' => $input->getOption('projects-dir'),
        'original-source' => array(
            'name' => $input->getOption('original-source.name'),
            'type' => $input->getOption('original-source.type'),
            'url' => $input->getOption('original-source.url'),
        )
    );

    $manager->createProject($name, $template, $options);
  }

}
