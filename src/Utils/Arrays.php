<?php

/*
 * This file is part of Alchemist.
 *
 * (c) Lukáš Drahník <ldrahnik@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemist\Utils;

/**
 * @author Lukáš Drahník (http://ldrahnik.com)
 */
class Arrays
{

    /**
     * @param array $arr1
     * @param array $arr2
     *
     * @return mixed
     */
    static function merge($arr1, $arr2)
    {
        foreach ($arr2 as $key => $value) {
            if (array_key_exists($key, $arr1) && is_array($value)) {
                $arr1[$key] = Arrays::merge($arr1[$key], $arr2[$key]);
            } else {
                $arr1[$key] = $value;
            }
        }
        return $arr1;
    }

}
