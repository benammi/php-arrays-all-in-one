<?php

use Benammi\ArrayAIO\ArrayAIO;

it('splits a string into an array with a separator', function () {
    expect(ArrayAIO::stringToArray('hello hamza', ' '))->toEqual(['hello', 'hamza']);
});

it('splits a string into characters when separator is empty', function () {
    expect(ArrayAIO::stringToArray('ab'))->toEqual(['a', 'b']);
    expect(ArrayAIO::stringToArray(''))->toEqual([]);
});

it('joins an array into a string', function () {
    expect(ArrayAIO::arrayToString(['a', 'b'], '-'))->toEqual('a-b');
    expect(ArrayAIO::implode(['a', 'b'], '-'))->toEqual('a-b');
});

it('joins with a final separator', function () {
    expect(ArrayAIO::join(['a', 'b', 'c'], ', ', ' and '))->toEqual('a, b and c');
    expect(ArrayAIO::join(['a'], ', ', ' and '))->toEqual('a');
    expect(ArrayAIO::join([], ', ', ' and '))->toEqual('');
});

it('wraps values into arrays', function () {
    expect(ArrayAIO::wrap(null))->toEqual([]);
    expect(ArrayAIO::wrap('x'))->toEqual(['x']);
    expect(ArrayAIO::wrap([1, 2]))->toEqual([1, 2]);
});

it('converts iterables and objects', function () {
    expect(ArrayAIO::fromIterable(new \ArrayIterator(['a' => 1])))->toEqual(['a' => 1]);
    expect(ArrayAIO::fromObject((object) ['name' => 'Hamza']))->toEqual(['name' => 'Hamza']);
    expect(ArrayAIO::toObject(['name' => 'Hamza']))->toEqual((object) ['name' => 'Hamza']);
});

it('builds ranges, combines and fills', function () {
    expect(ArrayAIO::range(1, 3))->toEqual([1, 2, 3]);
    expect(ArrayAIO::combine(['a', 'b'], [1, 2]))->toEqual(['a' => 1, 'b' => 2]);
    expect(ArrayAIO::fill(0, 2, 'x'))->toEqual([0 => 'x', 1 => 'x']);
    expect(ArrayAIO::fillKeys(['a', 'b'], 0))->toEqual(['a' => 0, 'b' => 0]);
});

it('merges and replaces arrays', function () {
    expect(ArrayAIO::merge([1, 2], [3], ['r']))->toEqual([1, 2, 3, 'r']);
    expect(ArrayAIO::mergeArrays([1, 2], [3], ['r']))->toEqual([1, 2, 3, 'r']);
    expect(ArrayAIO::mergeRecursive(['a' => [1]], ['a' => [2]]))->toEqual(['a' => [1, 2]]);
    expect(ArrayAIO::concat([1], [2, 3]))->toEqual([1, 2, 3]);
    expect(ArrayAIO::replace(['a' => 1, 'b' => 2], ['b' => 9]))->toEqual(['a' => 1, 'b' => 9]);
    expect(ArrayAIO::replaceRecursive(['a' => ['b' => 1]], ['a' => ['c' => 2]]))->toEqual(['a' => ['b' => 1, 'c' => 2]]);
});

it('gets and sets values including dotted paths', function () {
    $data = ['user' => ['name' => 'Hamza', 'city' => 'Rabat']];

    expect(ArrayAIO::get($data, 'user'))->toEqual(['name' => 'Hamza', 'city' => 'Rabat']);
    expect(ArrayAIO::get($data, 'missing', 'fallback'))->toEqual('fallback');
    expect(ArrayAIO::getDot($data, 'user.name'))->toEqual('Hamza');
    expect(ArrayAIO::getDot($data, 'user.missing', 'n/a'))->toEqual('n/a');
    expect(ArrayAIO::set($data, 'active', true))->toEqual(['user' => ['name' => 'Hamza', 'city' => 'Rabat'], 'active' => true]);
    expect(ArrayAIO::setDot($data, 'user.city', 'Casablanca')['user']['city'])->toEqual('Casablanca');
    expect(ArrayAIO::hasDot($data, 'user.name'))->toBeTrue();
    expect(ArrayAIO::hasDot($data, 'user.age'))->toBeFalse();
});

it('forgets keys and dotted paths without mutating the original', function () {
    $data = ['a' => 1, 'b' => 2, 'user' => ['name' => 'Hamza', 'city' => 'Rabat']];
    $original = $data;

    expect(ArrayAIO::forget($data, 'a'))->toEqual(['b' => 2, 'user' => ['name' => 'Hamza', 'city' => 'Rabat']]);
    expect(ArrayAIO::forgetDot($data, 'user.city'))->toEqual(['a' => 1, 'b' => 2, 'user' => ['name' => 'Hamza']]);
    expect($data)->toEqual($original);
});

it('checks presence of keys and values', function () {
    $data = ['a' => 1, 'b' => 2];

    expect(ArrayAIO::has($data, 'a'))->toBeTrue();
    expect(ArrayAIO::has($data, ['a', 'b']))->toBeTrue();
    expect(ArrayAIO::has($data, ['a', 'c']))->toBeFalse();
    expect(ArrayAIO::hasAny($data, ['c', 'b']))->toBeTrue();
    expect(ArrayAIO::missing($data, 'c'))->toBeTrue();
    expect(ArrayAIO::contains($data, 2))->toBeTrue();
    expect(ArrayAIO::contains($data, '2'))->toBeFalse();
});

it('selects keys, values, only and except', function () {
    $data = ['a' => 1, 'b' => 2, 'c' => 3];

    expect(ArrayAIO::keys($data))->toEqual(['a', 'b', 'c']);
    expect(ArrayAIO::values($data))->toEqual([1, 2, 3]);
    expect(ArrayAIO::only($data, ['a', 'c']))->toEqual(['a' => 1, 'c' => 3]);
    expect(ArrayAIO::except($data, ['b']))->toEqual(['a' => 1, 'c' => 3]);
});

it('reads first, last and nth values', function () {
    $data = ['a' => 10, 'b' => 20, 'c' => 30];

    expect(ArrayAIO::first($data))->toEqual(10);
    expect(ArrayAIO::last($data))->toEqual(30);
    expect(ArrayAIO::nth($data, 1))->toEqual(20);
    expect(ArrayAIO::first($data, fn ($v) => $v > 15))->toEqual(20);
    expect(ArrayAIO::last($data, fn ($v) => $v < 25))->toEqual(20);
    expect(ArrayAIO::first([], null, 'x'))->toEqual('x');
});

it('pushes, prepends and removes edges immutably', function () {
    $data = [1, 2];

    expect(ArrayAIO::push($data, 3, 4))->toEqual([1, 2, 3, 4]);
    expect(ArrayAIO::prepend($data, 0))->toEqual([0, 1, 2]);
    expect(ArrayAIO::withoutFirst($data))->toEqual([2]);
    expect(ArrayAIO::withoutLast($data))->toEqual([1]);
    expect(ArrayAIO::insert($data, 1, 9))->toEqual([1, 9, 2]);
    expect($data)->toEqual([1, 2]);
});

it('filters, rejects and queries rows', function () {
    $rows = [
        ['id' => 1, 'role' => 'admin'],
        ['id' => 2, 'role' => 'user'],
        ['id' => 3, 'role' => 'user'],
    ];

    expect(ArrayAIO::filter([0, 1, '', 2]))->toEqual([1 => 1, 3 => 2]);
    expect(ArrayAIO::reject([1, 2, 3], fn ($v) => $v === 2))->toEqual([0 => 1, 2 => 3]);
    expect(array_values(ArrayAIO::where($rows, 'role', 'user')))->toEqual([
        ['id' => 2, 'role' => 'user'],
        ['id' => 3, 'role' => 'user'],
    ]);
    expect(array_values(ArrayAIO::whereIn($rows, 'id', [1, 3])))->toHaveCount(2);
    expect(array_values(ArrayAIO::whereNotIn($rows, 'role', ['admin'])))->toHaveCount(2);
});

it('finds, searches and deduplicates', function () {
    $data = ['a' => 1, 'b' => 2, 'c' => 2, 'd' => 3];

    expect(ArrayAIO::find($data, fn ($v) => $v > 2))->toEqual(3);
    expect(ArrayAIO::findKey($data, fn ($v) => $v === 2))->toEqual('b');
    expect(ArrayAIO::search($data, 3))->toEqual('d');
    expect(ArrayAIO::unique([1, 2, 2, 3]))->toEqual([0 => 1, 1 => 2, 3 => 3]);
    expect(ArrayAIO::duplicates([1, 2, 2, 3, 3, 3]))->toEqual([2, 3]);
});

it('maps, plucks and reduces', function () {
    expect(ArrayAIO::map([1, 2], fn ($v) => $v * 2))->toEqual([2, 4]);
    expect(ArrayAIO::mapWithKeys(['a', 'b'], fn ($v, $k) => [$v => $k]))->toEqual(['a' => 0, 'b' => 1]);
    expect(ArrayAIO::mapKeys(['a' => 1], fn ($k) => strtoupper($k)))->toEqual(['A' => 1]);
    expect(ArrayAIO::flatMap([1, 2], fn ($v) => [$v, $v]))->toEqual([1, 1, 2, 2]);
    expect(ArrayAIO::pluck([['id' => 1, 'name' => 'A'], ['id' => 2, 'name' => 'B']], 'name', 'id'))
        ->toEqual([1 => 'A', 2 => 'B']);
    expect(ArrayAIO::reduce([1, 2, 3], fn ($c, $v) => $c + $v, 0))->toEqual(6);
    expect(ArrayAIO::pipe([1, 2], fn ($arr) => array_sum($arr)))->toEqual(3);
});

it('sorts values and keys', function () {
    expect(ArrayAIO::values(ArrayAIO::sort(['b' => 2, 'a' => 1])))->toEqual([1, 2]);
    expect(ArrayAIO::values(ArrayAIO::sortDesc(['a' => 1, 'b' => 2])))->toEqual([2, 1]);
    expect(ArrayAIO::values(ArrayAIO::sortBy([['n' => 2], ['n' => 1]], 'n')))->toEqual([['n' => 1], ['n' => 2]]);
    expect(ArrayAIO::values(ArrayAIO::sortByDesc([['n' => 1], ['n' => 2]], 'n')))->toEqual([['n' => 2], ['n' => 1]]);
    expect(ArrayAIO::sortKeys(['b' => 1, 'a' => 2]))->toEqual(['a' => 2, 'b' => 1]);
    expect(ArrayAIO::sortKeysDesc(['a' => 1, 'b' => 2]))->toEqual(['b' => 2, 'a' => 1]);
    expect(ArrayAIO::reverse([1, 2, 3]))->toEqual([3, 2, 1]);
});

it('diffs, intersects, chunks and slices', function () {
    expect(ArrayAIO::diff([1, 2, 3], [2]))->toEqual([0 => 1, 2 => 3]);
    expect(ArrayAIO::intersect([1, 2, 3], [2, 3, 4]))->toEqual([1 => 2, 2 => 3]);
    expect(ArrayAIO::chunk([1, 2, 3, 4], 2))->toEqual([[1, 2], [3, 4]]);
    expect(ArrayAIO::slice([1, 2, 3, 4], 1, 2))->toEqual([2, 3]);
    expect(ArrayAIO::take([1, 2, 3, 4], 2))->toEqual([1, 2]);
    expect(ArrayAIO::skip([1, 2, 3, 4], 2))->toEqual([2 => 3, 3 => 4]);
});

it('partitions, flattens, dots and zips', function () {
    [$even, $odd] = ArrayAIO::partition([1, 2, 3, 4], fn ($v) => $v % 2 === 0);
    expect(array_values($even))->toEqual([2, 4]);
    expect(array_values($odd))->toEqual([1, 3]);

    expect(ArrayAIO::flatten([1, [2, [3]]], 1))->toEqual([1, 2, [3]]);
    expect(ArrayAIO::flatten([1, [2, [3]]]))->toEqual([1, 2, 3]);
    expect(ArrayAIO::collapse([[1, 2], [3]]))->toEqual([1, 2, 3]);
    expect(ArrayAIO::dot(['user' => ['name' => 'Hamza']]))->toEqual(['user.name' => 'Hamza']);
    expect(ArrayAIO::undot(['user.name' => 'Hamza']))->toEqual(['user' => ['name' => 'Hamza']]);
    expect(ArrayAIO::zip([1, 2], ['a', 'b']))->toEqual([[1, 'a'], [2, 'b']]);
    expect(ArrayAIO::crossJoin([1, 2], ['a']))->toEqual([[1, 'a'], [2, 'a']]);
    expect(ArrayAIO::pad([1, 2], 4, 0))->toEqual([1, 2, 0, 0]);
});

it('aggregates and inspects arrays', function () {
    expect(ArrayAIO::count([1, 2]))->toEqual(2);
    expect(ArrayAIO::isEmpty([]))->toBeTrue();
    expect(ArrayAIO::isNotEmpty([1]))->toBeTrue();
    expect(ArrayAIO::sum([1, 2, 3]))->toEqual(6);
    expect(ArrayAIO::average([2, 4]))->toEqual(3);
    expect(ArrayAIO::avg([]))->toBeNull();
    expect(ArrayAIO::min([3, 1, 2]))->toEqual(1);
    expect(ArrayAIO::max([3, 1, 2]))->toEqual(3);
    expect(ArrayAIO::product([2, 3]))->toEqual(6);
});

it('supports every, some, keyBy, groupBy and flip', function () {
    expect(ArrayAIO::every([2, 4], fn ($v) => $v % 2 === 0))->toBeTrue();
    expect(ArrayAIO::some([1, 2], fn ($v) => $v === 2))->toBeTrue();
    expect(ArrayAIO::any([1, 2], fn ($v) => $v === 3))->toBeFalse();
    expect(ArrayAIO::keyBy([['id' => 1], ['id' => 2]], 'id'))->toEqual([1 => ['id' => 1], 2 => ['id' => 2]]);
    expect(ArrayAIO::groupBy([['t' => 'a'], ['t' => 'a'], ['t' => 'b']], 't'))
        ->toEqual(['a' => [['t' => 'a'], ['t' => 'a']], 'b' => [['t' => 'b']]]);
    expect(ArrayAIO::flip(['a' => 1, 'b' => 2]))->toEqual([1 => 'a', 2 => 'b']);
});

it('supports tap, when, unless, ensure and random', function () {
    $seen = null;
    expect(ArrayAIO::tap([1], function ($arr) use (&$seen) {
        $seen = $arr;
    }))->toEqual([1]);
    expect($seen)->toEqual([1]);

    expect(ArrayAIO::when([1], true, fn ($arr) => ArrayAIO::push($arr, 2)))->toEqual([1, 2]);
    expect(ArrayAIO::when([1], false, fn ($arr) => ArrayAIO::push($arr, 2)))->toEqual([1]);
    expect(ArrayAIO::unless([1], false, fn ($arr) => ArrayAIO::push($arr, 2)))->toEqual([1, 2]);
    expect(ArrayAIO::ensure(null))->toEqual([]);
    expect(ArrayAIO::ensure('x'))->toEqual(['x']);
    expect(ArrayAIO::random([7]))->toEqual(7);
    expect(ArrayAIO::random([1, 2, 3], 2))->toHaveCount(2);
});

it('throws on invalid inputs', function () {
    expect(fn () => ArrayAIO::range(1, 3, 0))->toThrow(\InvalidArgumentException::class);
    expect(fn () => ArrayAIO::combine([1], [1, 2]))->toThrow(\InvalidArgumentException::class);
    expect(fn () => ArrayAIO::chunk([1], 0))->toThrow(\InvalidArgumentException::class);
    expect(fn () => ArrayAIO::random([]))->toThrow(\InvalidArgumentException::class);
});
