<?php

use Hamza\ArrayAIO\ArrayAIO;

it('string to array test', function () {
    $arr = ArrayAIO::stringToArray('hello hamza', ' ');
    expect(count($arr))->toEqual(2);
});
