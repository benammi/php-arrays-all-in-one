<?php

namespace Hamza\ArrayAIO;

class ArrayAIO
{
    public static function stringToArray(string $str, string $separator = ''): array
    {
        $arr = explode($separator, $str);

        return $arr;
    }

    public static function mergeArrays(array ...$arrays): array
    {
        $arr = array_merge(...$arrays);

        return $arr;
    }
}
