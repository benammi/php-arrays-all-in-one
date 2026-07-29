# PHP Arrays All In One

[![Latest Version on Packagist](https://img.shields.io/packagist/v/benammi/php-arrays-all-in-one.svg?style=flat-square)](https://packagist.org/packages/benammi/php-arrays-all-in-one)
[![Tests](https://github.com/benammi/php-arrays-all-in-one/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/benammi/php-arrays-all-in-one/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/benammi/php-arrays-all-in-one.svg?style=flat-square)](https://packagist.org/packages/benammi/php-arrays-all-in-one)

A safe, typed, fully documented toolkit for everyday PHP array work.

It ships two APIs:

- **`collect()` / `Collection`** — a fluent, Laravel-style collection you can chain
- **`ArrayAIO`** — static helpers when you prefer plain arrays

**Requirements:** PHP 8.1+

## Installation

```bash
composer require benammi/php-arrays-all-in-one
```

```php
use Benammi\ArrayAIO\ArrayAIO;
use Benammi\ArrayAIO\Collection;
```

## Collection (Laravel-style)

```php
$users = collect([
    ['name' => 'Hamza', 'active' => true, 'score' => 20],
    ['name' => 'Sara', 'active' => false, 'score' => 35],
    ['name' => 'Omar', 'active' => true, 'score' => 15],
]);

$names = $users
    ->where('active', true)
    ->sortBy('score')
    ->pluck('name')
    ->values()
    ->all();
// ['Omar', 'Hamza']

collect([1, 2, 3, 4])
    ->filter(fn ($n) => $n % 2 === 0)
    ->map(fn ($n) => $n * 10)
    ->sum();
// 60
```

`Collection` implements `ArrayAccess`, `Countable`, `IteratorAggregate`, and `JsonSerializable`.
Almost every transform returns a **new** collection so chaining stays safe.

### Creating collections

| Method | Description |
| --- | --- |
| `collect($items)` | Global helper |
| `Collection::make($items)` | Explicit constructor helper |
| `Collection::range($start, $end, $step = 1)` | Numeric/string range |
| `Collection::times($n, $callback = null)` | Build N items |
| `Collection::wrap($value)` / `unwrap($value)` | Normalize to/from collection |

### Popular methods

Filtering: `filter`, `reject`, `where`, `whereIn`, `whereBetween`, `whereNull`, `whereNotNull`, `whereInstanceOf`  
Transforming: `map`, `mapWithKeys`, `flatMap`, `transform`, `each`, `reduce`, `pipe`  
Retrieving: `get`, `first`, `last`, `sole`, `value`, `pull`, `random`, `pluck`  
Organizing: `groupBy`, `keyBy`, `countBy`, `sort`, `sortBy`, `sortDesc`, `reverse`, `shuffle`  
Slicing: `chunk`, `slice`, `take`, `skip`, `forPage`, `sliding`, `split`, `nth`  
Combining: `merge`, `concat`, `union`, `zip`, `crossJoin`, `collapse`, `flatten`, `dot`, `undot`  
Aggregates: `sum`, `avg`, `median`, `mode`, `min`, `max`, `percentage`, `count`  
Conditionals: `when`, `unless`, `whenEmpty`, `whenNotEmpty`, `tap`  
Output: `all`, `toArray`, `toJson`, `implode`, `join`

Higher-order messages work too: `collect($rows)->map->name`.

## ArrayAIO static helpers

Use `Benammi\ArrayAIO\ArrayAIO` when you want predictable helpers with clear intent: immutable returns, dotted-key access, filtering, mapping, sorting, merging, and aggregation — without mutating the arrays you pass in.

## Quick examples

```php
ArrayAIO::stringToArray('hello world', ' ');
// ['hello', 'world']

ArrayAIO::merge([1, 2], [3], ['r']);
// [1, 2, 3, 'r']

ArrayAIO::getDot(['user' => ['name' => 'Hamza']], 'user.name');
// 'Hamza'

ArrayAIO::pluck([
    ['id' => 1, 'name' => 'A'],
    ['id' => 2, 'name' => 'B'],
], 'name', 'id');
// [1 => 'A', 2 => 'B']
```

Safety notes:

- Collection transforms return new instances (except mutating helpers like `transform`, `pull`, `pop`, `shift`, `splice`).
- `ArrayAIO` methods never mutate the input array; they return a new value.
- Missing keys use an explicit `$default` instead of notices/warnings.
- Invalid arguments throw `InvalidArgumentException`.
- `sole()` / `firstOrFail()` throw `ItemNotFoundException` / `MultipleItemsFoundException` when appropriate.

---

## ArrayAIO API reference

All methods are `public static` on `ArrayAIO`.

### Conversion & creation

| Method | Description |
| --- | --- |
| `stringToArray(string $str, string $separator = '')` | Split a string. Empty separator uses `str_split`. |
| `arrayToString(array $array, string $separator = '')` | Join values into a string. |
| `implode(array $array, string $separator = '')` | Alias of `arrayToString()`. |
| `join(array $array, string $separator, string $finalSeparator = '')` | Join with a different separator before the last item. |
| `wrap(mixed $value)` | Wrap a value in an array (`null` → `[]`). |
| `ensure(mixed $value)` | Normalize arrays, Traversables, scalars, and `null`. |
| `fromIterable(iterable $items)` | Convert any iterable to an array. |
| `fromObject(object $object)` | Public object properties as an array. |
| `toObject(array $array)` | Cast an array to `stdClass`. |
| `range($start, $end, $step = 1)` | Build a range (throws if `$step` is `0`). |
| `combine(array $keys, array $values)` | Pair keys with values (same length required). |
| `fill(int $startIndex, int $count, mixed $value)` | Fill with a value. |
| `fillKeys(array $keys, mixed $value)` | Fill using given keys. |

### Access

| Method | Description |
| --- | --- |
| `get(array $array, int\|string $key, mixed $default = null)` | Get a value or default. |
| `getDot(array $array, string $path, mixed $default = null)` | Get a nested value (`user.address.city`). |
| `has(array $array, int\|string\|array $keys)` | Whether all keys exist. |
| `hasAny(array $array, array $keys)` | Whether any key exists. |
| `hasDot(array $array, string $path)` | Whether a dotted path exists. |
| `missing(array $array, int\|string\|array $keys)` | Inverse of `has()`. |
| `contains(array $array, mixed $value, bool $strict = true)` | Value search. |
| `keys(array $array)` | All keys. |
| `values(array $array)` | All values (re-indexed). |
| `only(array $array, array $keys)` | Keep selected keys. |
| `except(array $array, array $keys)` | Drop selected keys. |
| `first(array $array, ?callable $callback = null, mixed $default = null)` | First value (optionally filtered). |
| `last(array $array, ?callable $callback = null, mixed $default = null)` | Last value (optionally filtered). |
| `nth(array $array, int $index, mixed $default = null)` | Value at zero-based position. |

### Mutation-style helpers (immutable)

| Method | Description |
| --- | --- |
| `set(array $array, int\|string $key, mixed $value)` | Set a key. |
| `setDot(array $array, string $path, mixed $value)` | Set a nested dotted path. |
| `forget(array $array, int\|string\|array $keys)` | Remove keys. |
| `forgetDot(array $array, string\|array $paths)` | Remove dotted paths. |
| `push(array $array, mixed ...$values)` | Append values. |
| `prepend(array $array, mixed ...$values)` | Prepend values. |
| `withoutFirst(array $array)` | Drop the first element. |
| `withoutLast(array $array)` | Drop the last element. |
| `insert(array $array, int $offset, mixed ...$values)` | Insert at offset. |
| `replace(array $array, array ...$replacements)` | `array_replace`. |
| `replaceRecursive(array $array, array ...$replacements)` | Recursive replace. |

### Filtering & search

| Method | Description |
| --- | --- |
| `filter(array $array, ?callable $callback = null, int $mode = 0)` | Filter values. |
| `reject(array $array, callable $callback)` | Inverse filter. |
| `where(array $array, string\|int $key, mixed $value)` | Rows where `$key === $value`. |
| `whereIn(array $array, string\|int $key, array $values)` | Rows where key is in list. |
| `whereNotIn(array $array, string\|int $key, array $values)` | Rows where key is not in list. |
| `find(array $array, callable $callback, mixed $default = null)` | First matching value. |
| `findKey(array $array, callable $callback)` | First matching key. |
| `search(array $array, mixed $value, bool $strict = true)` | Key of value, or `false`. |
| `unique(array $array, int $flags = SORT_STRING)` | Unique values. |
| `duplicates(array $array)` | Values that appear more than once. |

### Mapping & reducing

| Method | Description |
| --- | --- |
| `map(array $array, callable $callback)` | Map values (`($value, $key)`). |
| `mapWithKeys(array $array, callable $callback)` | Map to new key/value pairs. |
| `mapKeys(array $array, callable $callback)` | Remap keys. |
| `flatMap(array $array, callable $callback)` | Map then collapse one level. |
| `pluck(array $array, string\|int $value, string\|int\|null $key = null)` | Pluck a column. |
| `reduce(array $array, callable $callback, mixed $initial = null)` | Reduce to one value. |
| `pipe(array $array, callable $callback)` | Pass through a callback. |

### Sorting

| Method | Description |
| --- | --- |
| `sort(array $array, int $flags = SORT_REGULAR)` | Sort by value ascending. |
| `sortDesc(array $array, int $flags = SORT_REGULAR)` | Sort by value descending. |
| `sortBy(array $array, callable\|string\|int $callback, int $options = SORT_REGULAR)` | Sort by key/callback. |
| `sortByDesc(array $array, callable\|string\|int $callback, int $options = SORT_REGULAR)` | Sort by key/callback desc. |
| `sortKeys(array $array, int $flags = SORT_REGULAR)` | Sort by key ascending. |
| `sortKeysDesc(array $array, int $flags = SORT_REGULAR)` | Sort by key descending. |
| `reverse(array $array, bool $preserveKeys = false)` | Reverse order. |
| `shuffle(array $array)` | Shuffle values. |

### Combining & splitting

| Method | Description |
| --- | --- |
| `merge(array ...$arrays)` | Merge arrays. |
| `mergeArrays(array ...$arrays)` | Alias of `merge()` (kept for compatibility). |
| `mergeRecursive(array ...$arrays)` | Recursive merge. |
| `concat(array $array, array ...$arrays)` | Concatenate and re-index. |
| `diff` / `diffAssoc` / `diffKeys` | Difference helpers. |
| `intersect` / `intersectAssoc` / `intersectKeys` | Intersection helpers. |
| `chunk(array $array, int $size, bool $preserveKeys = false)` | Split into chunks. |
| `slice(array $array, int $offset, ?int $length = null, bool $preserveKeys = false)` | Extract a slice. |
| `take(array $array, int $limit)` | Take first N (negative = from end). |
| `skip(array $array, int $count)` | Skip first N. |
| `partition(array $array, callable $callback)` | `[passed, failed]`. |
| `flatten(array $array, int\|float $depth = INF)` | Flatten nested arrays. |
| `collapse(array $array)` | Collapse one level. |
| `dot(array $array, string $prepend = '')` | Flatten to dotted keys. |
| `undot(array $array)` | Expand dotted keys. |
| `zip(array ...$arrays)` | Zip into tuples. |
| `crossJoin(array ...$arrays)` | All permutations. |
| `pad(array $array, int $size, mixed $value)` | Pad to length. |

### Aggregation & utilities

| Method | Description |
| --- | --- |
| `count(array $array)` | Element count. |
| `isEmpty(array $array)` / `isNotEmpty(array $array)` | Emptiness checks. |
| `sum` / `average` / `avg` / `min` / `max` / `product` | Numeric aggregates. |
| `every(array $array, callable $callback)` | All pass. |
| `some` / `any` | At least one passes. |
| `random(array $array, ?int $number = null)` | One value, or N values. |
| `keyBy(array $array, callable\|string\|int $keyBy)` | Re-key items. |
| `groupBy(array $array, callable\|string\|int $groupBy)` | Group items. |
| `flip(array $array)` | Flip keys and values. |
| `tap(array $array, callable $callback)` | Side-effect then return array. |
| `when` / `unless` | Conditionally transform. |

---

## Testing

```bash
composer install
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Hamza Benammi](https://github.com/benammi)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
