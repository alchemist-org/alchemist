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

use Alchemist\Console\Command\LoadGithubSourcesCommand;
use Alchemist\DistantSource;
use Tester\Assert;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class LoadGithubSourceCommandTest extends CommandTestCase
{

    public function testLoadGithubSource()
    {
        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::equal(0, count($defaultDistanceSource));

        $template = 'default';
        $projectsDir = 'nginx';

        $this->runCommand(
            $this->getCommand(LoadGithubSourcesCommand::class),
            [
                'username' => 'ldrahnik',
                '--projects-dir' => $projectsDir,
                '--template' => $template,
            ]
        );

        $defaultDistanceSource = $this->configurator->getConfig()->getDistantSource(DistantSource::DEFAULT_DISTANT_SOURCE);
        Assert::notEqual(0, count($defaultDistanceSource));
        Assert::equal($projectsDir, reset($defaultDistanceSource)['parameters']['projects-dir']);
        Assert::equal($template, reset($defaultDistanceSource)['core']['template']);
    }

}

(new LoadGithubSourceCommandTest($container))->run();
