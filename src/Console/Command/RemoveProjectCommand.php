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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Alchemist\Manager;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class RemoveProjectCommand extends Command {

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
      ->setName('remove-project')
      ->setDescription('Remove project')
      ->setDefinition(array(
        new InputArgument('name', InputArgument::REQUIRED, 'project name')
      ));
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $manager = $this->manager;

    $name = $input->getArgument('name');

    $manager->removeProject($name);
  }

}
