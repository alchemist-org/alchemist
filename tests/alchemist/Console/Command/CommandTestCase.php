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
use Alchemist\DistantSource;
use Alchemist\Template;
use Nette\DI\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Alchemist\Console\Command\CreateProjectCommand;
use Tester\TestCase;
use Tester\Assert;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
abstract class CommandTestCase extends TestCase
{

    /** @var string */
    const PROJECTS_DIR_NAME = 'default'; // TODO: renamed from defaultt, idk why
    /** @var string */
    const APP_DATA_DIR = __DIR__ . '/../../../../data';
    /** @var string */
    const TEMPLATES = self::APP_DATA_DIR . '/templates';
    /** @var string */
    const TEMPLATE_NAME = Template::DEFAULT_TEMPLATE;
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
    /** @var Application */
    protected $console;
    /** @var Configurator */
    protected $configurator;
    /** @var Manager */
    protected $manager;
    /** @var Container */
    protected $container;
    /** @var array */
    private $config = [
        'parameters' => [
            'hosts' => self::HOSTS_FILE,
            'tld' => self::TLD,
            'localhost' => self::LOCALHOST,
            'nginx-sites-enabled' => self::NGINX_SITES_ENABLED,
            'nginx-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/nginx.default',
            'apache-sites-enabled' => self::APACHE_SITES_ENABLED,
            'apache-virtual-host-default' => self::APP_DATA_DIR . '/virtual-hosts/apache.default',
            'port' => self::PORT,
            'root' => self::ROOT
        ],
        'core' => [
            'template' => Template::DEFAULT_TEMPLATE,
            'templates' => self::TEMPLATES,
            'projects-dirs' => [
                self::PROJECTS_DIR_NAME => TEST_PROJECTS_DIR,
                'nginx' => [
                    'path' => TEST_PROJECTS_DIR,
                    'template' => 'nginx'
                ],
                'apache' => [
                    'path' => TEST_PROJECTS_DIR,
                    'template' => 'apache'
                ],
            ]
        ]
    ];

    /**
     * CommandTestCase constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function createProject($projectName, $save, $projectsDir = null)
    {

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSource));

        if($projectsDir) {
            $this->runCommand(
                $this->getCommand(CreateProjectCommand::class),
                [
                    'name' => $projectName,
                    '--save' => $save,
                    '--projects-dir' => $projectsDir
                ]
            );
        } else {
            $this->runCommand(
                $this->getCommand(CreateProjectCommand::class),
                [
                    'name' => $projectName,
                    '--save' => $save
                ]
            );
        }

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::truthy(empty($defaultDistanceSource));
    }

    public function setUp()
    {
        parent::setUp();

        $this->clean();

        $this->console = $this->container->getByType(Application::class);
        $this->configurator = $this->container->getByType(Configurator::class);
        $this->configurator->setConfigFile(self::CONFIG_LOCAL);
        $this->configurator->setConfig(new Config($this->config));
    }

    public function tearDown() {
        parent::tearDown();

        $this->clean();
    }

    public function clean()
    {
        // test project dirs
        \Tester\Helpers::purge(TEST_PROJECTS_DIR);
        \Tester\Helpers::purge(TEST_PROJECTS_DIR2);
        \Tester\Helpers::purge(TEST_PROJECTS_DIR_NEW);
        \Tester\Helpers::purge(TEST_TEMP_DIR);

        // nginx files
        foreach(glob(self::NGINX_SITES_ENABLED . '/*') as $file) {
            if(is_file($file) && $file != '.gitkeep') {
                unlink($file);
            }
        }

        // apache files
        foreach(glob(self::APACHE_SITES_ENABLED . '/*') as $file) {
            if(is_file($file) && $file != '.gitkeep') {
                unlink($file);
            }
        }

        // hosts
        if(is_file(self::HOSTS_FILE)) {
            unlink(self::HOSTS_FILE);
        }

        // config local
        if(is_file(self::CONFIG_LOCAL)) {
            unlink(self::CONFIG_LOCAL);
        }
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
    public function runCommand(Command $command, array $input = [], array $options = [])
    {
        $command = $this->console->find($command->getName());
        $commandTester = new CommandTester($command);
        $commandTester->execute($input, $options);
        return $commandTester->getDisplay();
    }

    /**
     * Grep under file.
     *
     * @param string $file
     * @param string $string
     *
     * @return string
     */
    protected function isStringInFile($file, $string)
    {
        return exec('grep ' . escapeshellarg($string) . " $file") ? true : false;
    }

    /**
     * Get command object from string.
     *
     * @param string $string
     *
     * @return Command|null
     */
    protected function getCommand($string)
    {
        try {
            return $this->container->getByType($string);
        } catch (\Exception $exception) {
            return null;
        }
    }

}
