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

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Console
{

  /**
   * @param string $cmd
   *
   * @return string
   */
  public static function execute($cmd) {
    return shell_exec($cmd);
  }

}
