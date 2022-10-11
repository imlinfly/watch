<?php

/**
 * Created by PhpStorm.
 * User: LinFei
 * Created time 2022/10/22 22:24:33
 * E-mail: fly@eyabc.cn
 */
declare (strict_types=1);

namespace library;

class Helper
{
    /**
     * Splitting parameters into arrays and removing left and right spaces
     * @access public
     * @param string $separator
     * @param string $string
     * @return array|string[]
     */
    public static function split(string $separator, string $string)
    {
        $array = explode($separator, $string);

        if (empty($array) || (trim($array[0]) == '' && count($array) == 1)) {
            return [];
        }

        return array_map(fn(string $value) => trim($value), $array);
    }
}
