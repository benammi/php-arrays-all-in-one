<?php

namespace Hamza\ArrayAIO;

class ArrayAIO
{
    public static function stringToArray(string $str, string $separator = ''): array
    {
        $arr = explode($separator, $str);

        return $arr;
    }
}
