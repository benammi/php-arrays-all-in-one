<?php

use Benammi\ArrayAIO\Collection;
use Benammi\ArrayAIO\ItemNotFoundException;
use Benammi\ArrayAIO\MultipleItemsFoundException;

it('creates collections via make, collect helper and wrap', function () {
    expect(Collection::make([1, 2])->all())->toEqual([1, 2]);
    expect(collect(['a' => 1])->all())->toEqual(['a' => 1]);
    expect(Collection::wrap('x')->all())->toEqual(['x']);
    expect(Collection::unwrap(collect([1, 2])))->toEqual([1, 2]);
});

it('supports range and times', function () {
    expect(Collection::range(1, 3)->all())->toEqual([1, 2, 3]);
    expect(Collection::times(3, fn ($i) => $i * 10)->all())->toEqual([10, 20, 30]);
});

it('chains map filter values like laravel collections', function () {
    $result = collect([1, 2, 3, 4, 5])
        ->filter(fn ($n) => $n % 2 === 0)
        ->map(fn ($n) => $n * 10)
        ->values()
        ->all();

    expect($result)->toEqual([20, 40]);
});

it('gets puts forgets and pulls values', function () {
    $collection = collect(['name' => 'Hamza', 'city' => 'Rabat']);

    expect($collection->get('name'))->toEqual('Hamza');
    expect($collection->has('city'))->toBeTrue();
    expect($collection->put('active', true)->get('active'))->toBeTrue();
    expect($collection->forget('city')->has('city'))->toBeFalse();

    $mutable = collect(['a' => 1, 'b' => 2]);
    expect($mutable->pull('a'))->toEqual(1);
    expect($mutable->all())->toEqual(['b' => 2]);
});

it('pushes prepends pops and shifts', function () {
    expect(collect([1])->push(2, 3)->all())->toEqual([1, 2, 3]);
    expect(collect([2, 3])->prepend(1)->all())->toEqual([1, 2, 3]);

    $items = collect([1, 2, 3]);
    expect($items->pop())->toEqual(3);
    expect($items->all())->toEqual([1, 2]);

    $items = collect([1, 2, 3]);
    expect($items->shift())->toEqual(1);
    expect($items->all())->toEqual([2, 3]);
});

it('merges concats and replaces', function () {
    expect(collect([1])->merge([2])->all())->toEqual([1, 2]);
    expect(collect([1])->concat([2, 3])->all())->toEqual([1, 2, 3]);
    expect(collect(['a' => 1])->replace(['a' => 9])->all())->toEqual(['a' => 9]);
    expect(collect(['a' => 1])->union(['a' => 9, 'b' => 2])->all())->toEqual(['a' => 1, 'b' => 2]);
});

it('filters with where helpers', function () {
    $rows = collect([
        ['id' => 1, 'role' => 'admin', 'score' => 10],
        ['id' => 2, 'role' => 'user', 'score' => 20],
        ['id' => 3, 'role' => 'user', 'score' => 30],
        ['id' => 4, 'role' => null, 'score' => 5],
    ]);

    expect($rows->where('role', 'user')->count())->toEqual(2);
    expect($rows->where('score', '>', 15)->pluck('id')->all())->toEqual([2, 3]);
    expect($rows->whereIn('id', [1, 3])->pluck('id')->values()->all())->toEqual([1, 3]);
    expect($rows->whereBetween('score', [10, 20])->count())->toEqual(2);
    expect($rows->whereNull('role')->count())->toEqual(1);
    expect($rows->whereNotNull('role')->count())->toEqual(3);
});

it('plucks groups and keys items', function () {
    $rows = collect([
        ['id' => 1, 'type' => 'a', 'name' => 'One'],
        ['id' => 2, 'type' => 'a', 'name' => 'Two'],
        ['id' => 3, 'type' => 'b', 'name' => 'Three'],
    ]);

    expect($rows->pluck('name', 'id')->all())->toEqual([1 => 'One', 2 => 'Two', 3 => 'Three']);
    expect($rows->keyBy('id')->keys()->all())->toEqual([1, 2, 3]);
    expect($rows->groupBy('type')->get('a')->count())->toEqual(2);
    expect($rows->countBy('type')->all())->toEqual(['a' => 2, 'b' => 1]);
});

it('sorts and reverses', function () {
    expect(collect([3, 1, 2])->sort()->values()->all())->toEqual([1, 2, 3]);
    expect(collect([1, 3, 2])->sortDesc()->values()->all())->toEqual([3, 2, 1]);
    expect(collect([['n' => 2], ['n' => 1]])->sortBy('n')->pluck('n')->all())->toEqual([1, 2]);
    expect(collect(['b' => 1, 'a' => 2])->sortKeys()->keys()->all())->toEqual(['a', 'b']);
    expect(collect([1, 2, 3])->reverse()->values()->all())->toEqual([3, 2, 1]);
});

it('chunks slices takes and skips', function () {
    expect(collect([1, 2, 3, 4])->chunk(2)->map(fn ($c) => $c->values()->all())->all())->toEqual([[1, 2], [3, 4]]);
    expect(collect([1, 2, 3, 4])->slice(1, 2)->values()->all())->toEqual([2, 3]);
    expect(collect([1, 2, 3, 4])->take(2)->values()->all())->toEqual([1, 2]);
    expect(collect([1, 2, 3, 4])->skip(2)->values()->all())->toEqual([3, 4]);
    expect(collect([1, 2, 3, 4])->forPage(2, 2)->values()->all())->toEqual([3, 4]);
    expect(collect([1, 2, 3, 4, 5])->takeUntil(3)->values()->all())->toEqual([1, 2]);
    expect(collect([1, 2, 3, 4])->skipUntil(3)->values()->all())->toEqual([3, 4]);
});

it('flattens dots and collapses', function () {
    expect(collect([1, [2, [3]]])->flatten()->all())->toEqual([1, 2, 3]);
    expect(collect(['user' => ['name' => 'Hamza']])->dot()->all())->toEqual(['user.name' => 'Hamza']);
    expect(collect(['user.name' => 'Hamza'])->undot()->all())->toEqual(['user' => ['name' => 'Hamza']]);
    expect(collect([[1, 2], [3]])->collapse()->all())->toEqual([1, 2, 3]);
    expect(collect([[1 => 'a'], [2 => 'b']])->collapseWithKeys()->all())->toEqual([1 => 'a', 2 => 'b']);
});

it('aggregates sum avg median mode min max', function () {
    expect(collect([1, 2, 3, 4])->sum())->toEqual(10);
    expect(collect([2, 4])->avg())->toEqual(3);
    expect(collect([1, 2, 3])->median())->toEqual(2);
    expect(collect([1, 2, 2, 3])->mode()->all())->toEqual([2]);
    expect(collect([3, 1, 2])->min())->toEqual(1);
    expect(collect([3, 1, 2])->max())->toEqual(3);
    expect(collect([1, 2, 3, 4])->percentage(fn ($n) => $n % 2 === 0))->toEqual(50.0);
});

it('supports contains unique duplicates and sole', function () {
    expect(collect([1, 2, 3])->contains(2))->toBeTrue();
    expect(collect([1, 2, 3])->doesntContain(9))->toBeTrue();
    expect(collect([1, 2, 2, 3])->unique()->values()->all())->toEqual([1, 2, 3]);
    expect(collect([1, 2, 2, 3])->duplicates()->values()->all())->toEqual([2]);
    expect(collect([5])->sole())->toEqual(5);
    expect(fn () => collect([])->sole())->toThrow(ItemNotFoundException::class);
    expect(fn () => collect([1, 2])->sole())->toThrow(MultipleItemsFoundException::class);
});

it('supports when unless tap pipe and join', function () {
    expect(collect([1])->when(true, fn ($c) => $c->push(2))->all())->toEqual([1, 2]);
    expect(collect([1])->unless(true, fn ($c) => $c->push(2))->all())->toEqual([1]);

    $seen = null;
    collect([1])->tap(function ($c) use (&$seen) {
        $seen = $c->all();
    });
    expect($seen)->toEqual([1]);

    expect(collect([1, 2])->pipe(fn ($c) => $c->sum()))->toEqual(3);
    expect(collect(['a', 'b', 'c'])->join(', ', ' and '))->toEqual('a, b and c');
    expect(collect(['a', 'b'])->implode('-'))->toEqual('a-b');
});

it('supports after before zip and crossJoin', function () {
    expect(collect([1, 2, 3])->after(2))->toEqual(3);
    expect(collect([1, 2, 3])->before(2))->toEqual(1);
    expect(collect([1, 2])->zip(['a', 'b'])->map->all()->all())->toEqual([[1, 'a'], [2, 'b']]);
    expect(collect([1, 2])->crossJoin(['a'])->all())->toEqual([[1, 'a'], [2, 'a']]);
});

it('implements array access countable and json', function () {
    $collection = collect(['a' => 1]);
    expect(isset($collection['a']))->toBeTrue();
    expect($collection['a'])->toEqual(1);
    expect(count($collection))->toEqual(1);
    expect($collection->toJson())->toEqual('{"a":1}');
    expect(iterator_to_array($collection))->toEqual(['a' => 1]);
});

it('partitions and ensures types', function () {
    [$even, $odd] = collect([1, 2, 3, 4])->partition(fn ($n) => $n % 2 === 0)->all();
    expect($even->values()->all())->toEqual([2, 4]);
    expect($odd->values()->all())->toEqual([1, 3]);
    expect(collect([1, 2])->ensure('integer')->count())->toEqual(2);
    expect(fn () => collect([1, 'x'])->ensure('integer'))->toThrow(InvalidArgumentException::class);
});
