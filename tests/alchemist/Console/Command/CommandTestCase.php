<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Console\Command;

use Alchemist\Config;
use Alchemist\Configurator;
use Alchemist\Manager;
use Nette\DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tester\TestCase;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
abstract class CommandTestCase extends TestCase
{
    /** @var Application */
    protected $console;

    /** @var Configurator */
    protected $configurator;

    /** @var Manager */
    protected $manager;

    /** @var string */
    const PROJECTS_DIR_NAME = 'defaultt';

    /** @var string */
    const TEMPLATES = __DIR__ . '/../data/templates';

    /** @var string */
    const TEMPLATE_NAME = 'default';

    /** @var string */
    const CONFIG_LOCAL = __DIR__ . '/../data/config/config.local.neon';

    /** @var array */
    private $config = array(
        'parameters' => array(),
        'core' => array(
            'template' => 'default',
            'templates' => self::TEMPLATES,
            'projects-dirs' => array(
                self::PROJECTS_DIR_NAME => TEST_PROJECTS_DIR
            )
        )
    );

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setUp()
    {
        parent::setUp();

        $this->console = $this->container->getByType(Application::class);

        /** @var Configurator $configurator */
        $this->configurator = $this->container->getByType(Configurator::class);
        $this->configurator->setConfigFile(self::CONFIG_LOCAL);
        $this->configurator->setConfig(new Config($this->config));
    }

    /**
     * Runs a command and returns it output.
     *
     * @param Command $command
     * @param array $input
     * @param array $options
     *
     * @return string
     */
    public function runCommand(Command $command, array $input = array(), array $options = array())
    {
        $command = $this->console->find($command->getName());
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);
        return $commandTester->getDisplay();
    }

}
