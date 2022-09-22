<?php

namespace Hamza\ArrayAIO;

class ArrayAIO
{
    public function stringToArray(string $str, string $separator=''):array
    {
        $arr = explode($str,$separator);
        return $arr;
    }
}
