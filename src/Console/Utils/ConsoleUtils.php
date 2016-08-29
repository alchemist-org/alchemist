<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist\Console\Utils;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class ConsoleUtils
{

    /**
     * @param mixed $value
     * @param OutputInterface $output
     *
     * @return void
     */
    public static function writeln($result, OutputInterface $output)
    {
        if(!is_array($result)) {
            $output->writeln($result);
        } else {
            foreach($result as $key => $value) {
                ConsoleUtils::writeln($value, $output);
            }
        }
    }

}