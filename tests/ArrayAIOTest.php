<?php

use Hamza\ArrayAIO\ArrayAIO;

it('string to array test', function () {
    $arr = ArrayAIO::stringToArray('hello hamza', ' ');
    expect(count($arr))->toEqual(2);
});


it('merge arrays test', function(){
    $arr = ArrayAIO::mergeArrays([1,2],[3],['r']);
    expect($arr)->toEqual([1,2,3,'r']);
});
