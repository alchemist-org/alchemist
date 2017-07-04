<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist;

use UrlMatcher\Matcher;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Parser
{

    /**
     * @param string $line
     * @param array $args
     *
     * @return string
     */
    static function parse($line, array $args = array())
    {
        $parser = new Matcher($line, $args, [
            'optional_lft' => '@',
            'optional_rgt' => '#'
        ]);
        return $parser->parse();
    }

}
