<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

process(is_array($argv) ? $argv : []);

/**
 * @string Default name for alias
 */
const DEFAULT_FILE_NAME = 'alchemist';

/**
 * Processes the installer
 */
function process($argv)
{
    $installDir = getOptValue('--install-dir', $argv, false);
    $filename = getOptValue('--filename', $argv, 'alchemist');
    $force = in_array('--force', $argv);

    if (!checkParams($installDir)) {
        exit(1);
    }

    $repositoryDir = $installDir . DIRECTORY_SEPARATOR . $filename;
    if (!isDirEmpty($repositoryDir)) {
        if ($force) {
            exec("rm -rf $repositoryDir");
        } else {
            printf("The defined repository directory ({$repositoryDir}) is not empty. \nUse --force option to force installation.\n", 'error');
            exit(1);
        }
    }

    $installer = new Installer();
    if ($installer->run($installDir, $filename)) {
        exit(0);
    }

    exit(1);
}

/**
 * @param string $dir
 *
 * @return bool|null
 */
function isDirEmpty($dir) {
    if (!is_readable($dir)) return null;
    return (count(scandir($dir)) == 2);
}

/**
 * @param mixed $installDir
 *
 * @return bool
 */
function checkParams($installDir)
{
    $result = true;

    if (false !== $installDir && !is_dir($installDir)) {
        printf("The defined install dir ({$installDir}) does not exist.\n", 'info');
        $result = false;
    }

    return $result;
}

/**
 * Returns the value of a command-line option
 *
 * @param string $opt The command-line option to check
 * @param array $argv Command-line arguments
 * @param mixed $default Default value to be returned
 *
 * @return mixed The command-line value or the default
 */
function getOptValue($opt, $argv, $default)
{
    $optLength = strlen($opt);

    foreach ($argv as $key => $value) {
        $next = $key + 1;
        if (0 === strpos($value, $opt)) {
            if ($optLength === strlen($value) && isset($argv[$next])) {
                return trim($argv[$next]);
            } else {
                return trim(substr($value, $optLength + 1));
            }
        }
    }

    return $default;
}

class Installer {

    /**
     * @string Alchemist repository url
     */
    const REPOSITORY_URL = "https://github.com/alchemist-org/alchemist.git";

    /**
     * @param string $installDir
     * @param string $filename
     *
     * @return int
     */
    function run($installDir, $filename) {
        $repositoryUrl = self::REPOSITORY_URL;
        $repositoryDir = $installDir . DIRECTORY_SEPARATOR . $filename;

        exec("git clone $repositoryUrl $repositoryDir");
        exec("cd $repositoryDir && composer install");

        return 0;
    }

}