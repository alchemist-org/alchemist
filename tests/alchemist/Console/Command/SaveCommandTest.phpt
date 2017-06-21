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

use Alchemist\Console\Command\SaveCommand;

$container = require_once __DIR__ . '/../../../bootstrap.php';

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 * @testCase
 */
class SaveCommandTest extends CommandTestCase
{

    public function testSave()
    {
        $this->runCommand(
             $this->getCommand(SaveCommand::class)
        );
    }

}

$testCase = new SaveCommandTest($container);
$testCase->run();
