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
    const APP_DATA_DIR = __DIR__ . '/../../../../data';

    /** @var string */
    const TEMPLATES = self::APP_DATA_DIR . '/templates';

    /** @var string */
    const TEMPLATE_NAME = 'default';

    /** @var string */
    const CONFIG_LOCAL = __DIR__ . '/../data/config/config.local.neon';

    /** @var string */
    const HOSTS_FILE = __DIR__ . '/../data/hosts';

    /** @var string */
    const NGINX_SITES_ENABLED = __DIR__ . '/../data/nginx/sites-enabled';

    /** @var string */
    const APACHE_SITES_ENABLED = __DIR__ . '/../data/apache/sites-enabled';

    /** @var string */
    const TLD = 'dev';

    /** @var integer */
    const PORT = 80;

    /** @var string */
    const ROOT = 'www';

    /** @var string */
    const LOCALHOST = '127.0.0.1';

    /** @var array */
    private $config = array(
        'parameters' => array(
            'hosts' => self::HOSTS_FILE,
            'tld' => self::TLD,
            'localhost' => self::LOCALHOST,
            'nginx-sites-enabled' => self::NGINX_SITES_ENABLED,
            'nginx-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/nginx.default',
            'apache-sites-enabled' => self::APACHE_SITES_ENABLED,
            'apache-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/apache.default',
            'port' => self::PORT,
            'root' => self::ROOT
        ),
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

    /**
     * @param string $file
     * @param string $string
     *
     * @return string
     */
    protected function isStringInFile($file, $string)
    {
        return exec('grep ' . escapeshellarg($string) . " $file") ? true : false;
    }

}
