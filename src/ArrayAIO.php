<?php

namespace Benammi\ArrayAIO;

use InvalidArgumentException;
use Traversable;

/**
 * A safe, typed, well-documented toolkit for everyday PHP array work.
 *
 * All methods are static and side-effect free: they never mutate the input
 * array you pass in. Prefer these helpers when you want predictable returns,
 * dotted-key access, and clearer intent than raw PHP array_* calls.
 */
class ArrayAIO
{
    /**
     * Split a string into an array.
     *
     * When $separator is an empty string, each character becomes an element
     * (equivalent to str_split). Otherwise explode() is used.
     *
     * @return list<string>
     */
    public static function stringToArray(string $str, string $separator = ''): array
    {
        if ($separator === '') {
            return $str === '' ? [] : str_split($str);
        }

        return explode($separator, $str);
    }

    /**
     * Join array elements into a string.
     *
     * @param array<int|string, mixed> $array
     */
    public static function arrayToString(array $array, string $separator = ''): string
    {
        return implode($separator, $array);
    }

    /**
     * Alias of arrayToString().
     *
     * @param array<int|string, mixed> $array
     */
    public static function implode(array $array, string $separator = ''): string
    {
        return self::arrayToString($array, $separator);
    }

    /**
     * Join values with a separator, using a different separator before the last item.
     *
     * Example: join(['a', 'b', 'c'], ', ', ' and ') => "a, b and c"
     *
     * @param array<int|string, mixed> $array
     */
    public static function join(array $array, string $separator, string $finalSeparator = ''): string
    {
        $values = array_values($array);
        $count = count($values);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return (string) $values[0];
        }

        if ($finalSeparator === '') {
            return implode($separator, $values);
        }

        $last = array_pop($values);

        return implode($separator, $values) . $finalSeparator . $last;
    }

    /**
     * Wrap a value in an array unless it is already an array.
     *
     * @return array<int|string, mixed>
     */
    public static function wrap(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value === null) {
            return [];
        }

        return [$value];
    }

    /**
     * Convert an iterable (array or Traversable) into a plain array.
     *
     * @param iterable<int|string, mixed> $items
     * @return array<int|string, mixed>
     */
    public static function fromIterable(iterable $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        return iterator_to_array($items);
    }

    /**
     * Convert an object to an associative array (public properties / ArrayAccess-like casts).
     *
     * @return array<string, mixed>
     */
    public static function fromObject(object $object): array
    {
        return get_object_vars($object);
    }

    /**
     * Convert an associative array to a stdClass object.
     *
     * @param array<int|string, mixed> $array
     */
    public static function toObject(array $array): object
    {
        return (object) $array;
    }

    /**
     * Create an array containing a range of elements.
     *
     * @return list<int|float|string>
     */
    public static function range(int|float|string $start, int|float|string $end, int|float $step = 1): array
    {
        if ($step == 0) {
            throw new InvalidArgumentException('Step cannot be zero.');
        }

        return range($start, $end, $step);
    }

    /**
     * Create an array by using one array for keys and another for values.
     *
     * @param array<int|string, int|string> $keys
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    public static function combine(array $keys, array $values): array
    {
        if (count($keys) !== count($values)) {
            throw new InvalidArgumentException('combine() requires $keys and $values to have the same length.');
        }

        return array_combine($keys, $values) ?: [];
    }

    /**
     * Fill an array with values.
     *
     * @return array<int, mixed>
     */
    public static function fill(int $startIndex, int $count, mixed $value): array
    {
        if ($count < 0) {
            throw new InvalidArgumentException('fill() count must be zero or greater.');
        }

        return array_fill($startIndex, $count, $value);
    }

    /**
     * Fill an array using the values of $keys as keys and $value for every entry.
     *
     * @param array<int|string, int|string> $keys
     * @return array<int|string, mixed>
     */
    public static function fillKeys(array $keys, mixed $value): array
    {
        return array_fill_keys($keys, $value);
    }

    /**
     * Merge one or more arrays (later values overwrite earlier ones for matching string keys).
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function merge(array ...$arrays): array
    {
        if ($arrays === []) {
            return [];
        }

        return array_merge(...$arrays);
    }

    /**
     * Backward-compatible alias of merge().
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function mergeArrays(array ...$arrays): array
    {
        return self::merge(...$arrays);
    }

    /**
     * Recursively merge one or more arrays.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        if ($arrays === []) {
            return [];
        }

        $result = array_shift($arrays);

        foreach ($arrays as $array) {
            $result = array_merge_recursive($result, $array);
        }

        return $result;
    }

    /**
     * Append the values of given arrays onto the first array (re-indexed).
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return list<mixed>
     */
    public static function concat(array $array, array ...$arrays): array
    {
        return array_values(array_merge($array, ...$arrays));
    }

    /**
     * Replace values recursively from left to right.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$replacements
     * @return array<int|string, mixed>
     */
    public static function replace(array $array, array ...$replacements): array
    {
        return array_replace($array, ...$replacements);
    }

    /**
     * Recursively replace values from left to right.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$replacements
     * @return array<int|string, mixed>
     */
    public static function replaceRecursive(array $array, array ...$replacements): array
    {
        return array_replace_recursive($array, ...$replacements);
    }

    /**
     * Get a value by key, or $default when missing.
     *
     * @param array<int|string, mixed> $array
     */
    public static function get(array $array, int|string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }

    /**
     * Get a nested value using "dot" notation (e.g. "user.address.city").
     *
     * @param array<int|string, mixed> $array
     */
    public static function getDot(array $array, string $path, mixed $default = null): mixed
    {
        if ($path === '') {
            return $default;
        }

        if (array_key_exists($path, $array)) {
            return $array[$path];
        }

        foreach (explode('.', $path) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set a value by key (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function set(array $array, int|string $key, mixed $value): array
    {
        $array[$key] = $value;

        return $array;
    }

    /**
     * Set a nested value using "dot" notation (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function setDot(array $array, string $path, mixed $value): array
    {
        if ($path === '') {
            throw new InvalidArgumentException('setDot() path cannot be empty.');
        }

        $keys = explode('.', $path);
        $result = $array;
        $cursor = &$result;

        foreach ($keys as $index => $key) {
            if ($index === count($keys) - 1) {
                $cursor[$key] = $value;

                break;
            }

            if (! isset($cursor[$key]) || ! is_array($cursor[$key])) {
                $cursor[$key] = [];
            }

            $cursor = &$cursor[$key];
        }

        return $result;
    }

    /**
     * Remove one or more keys (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @param int|string|array<int, int|string> $keys
     * @return array<int|string, mixed>
     */
    public static function forget(array $array, int|string|array $keys): array
    {
        foreach (self::wrap($keys) as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Remove nested values using "dot" notation (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @param string|array<int, string> $paths
     * @return array<int|string, mixed>
     */
    public static function forgetDot(array $array, string|array $paths): array
    {
        foreach (self::wrap($paths) as $path) {
            $keys = explode('.', (string) $path);
            $result = &$array;

            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (! isset($result[$key]) || ! is_array($result[$key])) {
                    continue 2;
                }

                $result = &$result[$key];
            }

            unset($result[array_shift($keys)]);
            unset($result);
        }

        return $array;
    }

    /**
     * Determine whether the given key (or all keys) exist.
     *
     * @param array<int|string, mixed> $array
     * @param int|string|array<int, int|string> $keys
     */
    public static function has(array $array, int|string|array $keys): bool
    {
        $keys = self::wrap($keys);

        if ($keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (! array_key_exists($key, $array)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether any of the given keys exist.
     *
     * @param array<int|string, mixed> $array
     * @param array<int, int|string> $keys
     */
    public static function hasAny(array $array, array $keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether a nested path exists using "dot" notation.
     *
     * @param array<int|string, mixed> $array
     */
    public static function hasDot(array $array, string $path): bool
    {
        if ($path === '') {
            return false;
        }

        if (array_key_exists($path, $array)) {
            return true;
        }

        foreach (explode('.', $path) as $segment) {
            if (! is_array($array) || ! array_key_exists($segment, $array)) {
                return false;
            }

            $array = $array[$segment];
        }

        return true;
    }

    /**
     * Determine whether the given key (or any of the keys) is missing.
     *
     * @param array<int|string, mixed> $array
     * @param int|string|array<int, int|string> $keys
     */
    public static function missing(array $array, int|string|array $keys): bool
    {
        return ! self::has($array, $keys);
    }

    /**
     * Determine whether the array contains the given value.
     *
     * @param array<int|string, mixed> $array
     */
    public static function contains(array $array, mixed $value, bool $strict = true): bool
    {
        return in_array($value, $array, $strict);
    }

    /**
     * Get all keys.
     *
     * @param array<int|string, mixed> $array
     * @return list<int|string>
     */
    public static function keys(array $array): array
    {
        return array_keys($array);
    }

    /**
     * Get all values (re-indexed).
     *
     * @param array<int|string, mixed> $array
     * @return list<mixed>
     */
    public static function values(array $array): array
    {
        return array_values($array);
    }

    /**
     * Return only the given keys.
     *
     * @param array<int|string, mixed> $array
     * @param array<int, int|string> $keys
     * @return array<int|string, mixed>
     */
    public static function only(array $array, array $keys): array
    {
        return array_intersect_key($array, array_flip($keys));
    }

    /**
     * Return all keys except the given ones.
     *
     * @param array<int|string, mixed> $array
     * @param array<int, int|string> $keys
     * @return array<int|string, mixed>
     */
    public static function except(array $array, array $keys): array
    {
        return array_diff_key($array, array_flip($keys));
    }

    /**
     * Get the first value, optionally matching a callback.
     *
     * @param array<int|string, mixed> $array
     * @param (callable(mixed, int|string): bool)|null $callback
     */
    public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            foreach ($array as $item) {
                return $item;
            }

            return $default;
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get the last value, optionally matching a callback.
     *
     * @param array<int|string, mixed> $array
     * @param (callable(mixed, int|string): bool)|null $callback
     */
    public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if ($callback === null) {
            if ($array === []) {
                return $default;
            }

            return end($array);
        }

        return self::first(array_reverse($array, true), $callback, $default);
    }

    /**
     * Get the value at a zero-based position among values (ignores original keys).
     *
     * @param array<int|string, mixed> $array
     */
    public static function nth(array $array, int $index, mixed $default = null): mixed
    {
        $values = array_values($array);

        return array_key_exists($index, $values) ? $values[$index] : $default;
    }

    /**
     * Push one or more values onto the end (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function push(array $array, mixed ...$values): array
    {
        foreach ($values as $value) {
            $array[] = $value;
        }

        return $array;
    }

    /**
     * Prepend one or more values to the beginning (returns a new array).
     *
     * Values are prepended in the order given, so the first argument becomes first.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function prepend(array $array, mixed ...$values): array
    {
        return array_merge($values, $array);
    }

    /**
     * Return the array without its first element.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function withoutFirst(array $array): array
    {
        $copy = $array;
        array_shift($copy);

        return $copy;
    }

    /**
     * Return the array without its last element.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function withoutLast(array $array): array
    {
        $copy = $array;
        array_pop($copy);

        return $copy;
    }

    /**
     * Insert values at the given offset (returns a new array, re-indexed list style).
     *
     * @param array<int|string, mixed> $array
     * @return list<mixed>
     */
    public static function insert(array $array, int $offset, mixed ...$values): array
    {
        $items = array_values($array);
        array_splice($items, $offset, 0, $values);

        return $items;
    }

    /**
     * Filter elements using a callback (or remove empty values when no callback).
     *
     * Callback signature: function(mixed $value, int|string $key): bool
     *
     * @param array<int|string, mixed> $array
     * @param (callable(mixed, int|string): bool)|null $callback
     * @return array<int|string, mixed>
     */
    public static function filter(array $array, ?callable $callback = null, int $mode = 0): array
    {
        if ($callback === null) {
            return array_filter($array);
        }

        return array_filter($array, $callback, $mode === 0 ? ARRAY_FILTER_USE_BOTH : $mode);
    }

    /**
     * Reject elements that pass the truth test (inverse of filter).
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     * @return array<int|string, mixed>
     */
    public static function reject(array $array, callable $callback): array
    {
        return array_filter(
            $array,
            static fn (mixed $value, int|string $key): bool => ! $callback($value, $key),
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Filter associative rows where $key equals $value (strict).
     *
     * @param array<int|string, array<int|string, mixed>> $array
     * @return array<int|string, array<int|string, mixed>>
     */
    public static function where(array $array, string|int $key, mixed $value): array
    {
        return array_filter(
            $array,
            static function (mixed $item) use ($key, $value): bool {
                if (! is_array($item)) {
                    return false;
                }

                return array_key_exists($key, $item) && $item[$key] === $value;
            }
        );
    }

    /**
     * Filter associative rows where $key is in $values (strict).
     *
     * @param array<int|string, array<int|string, mixed>> $array
     * @param array<int, mixed> $values
     * @return array<int|string, array<int|string, mixed>>
     */
    public static function whereIn(array $array, string|int $key, array $values): array
    {
        return array_filter(
            $array,
            static function (mixed $item) use ($key, $values): bool {
                if (! is_array($item) || ! array_key_exists($key, $item)) {
                    return false;
                }

                return in_array($item[$key], $values, true);
            }
        );
    }

    /**
     * Filter associative rows where $key is not in $values (strict).
     *
     * @param array<int|string, array<int|string, mixed>> $array
     * @param array<int, mixed> $values
     * @return array<int|string, array<int|string, mixed>>
     */
    public static function whereNotIn(array $array, string|int $key, array $values): array
    {
        return array_filter(
            $array,
            static function (mixed $item) use ($key, $values): bool {
                if (! is_array($item) || ! array_key_exists($key, $item)) {
                    return false;
                }

                return ! in_array($item[$key], $values, true);
            }
        );
    }

    /**
     * Find the first value that passes the truth test.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     */
    public static function find(array $array, callable $callback, mixed $default = null): mixed
    {
        return self::first($array, $callback, $default);
    }

    /**
     * Find the first key that passes the truth test.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     */
    public static function findKey(array $array, callable $callback): int|string|null
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $key;
            }
        }

        return null;
    }

    /**
     * Search for a value and return its key, or false if not found.
     *
     * @param array<int|string, mixed> $array
     */
    public static function search(array $array, mixed $value, bool $strict = true): int|string|false
    {
        return array_search($value, $array, $strict);
    }

    /**
     * Remove duplicate values.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function unique(array $array, int $flags = SORT_STRING): array
    {
        return array_unique($array, $flags);
    }

    /**
     * Return values that appear more than once.
     *
     * @param array<int|string, mixed> $array
     * @return list<mixed>
     */
    public static function duplicates(array $array): array
    {
        $seen = [];
        $duplicates = [];

        foreach ($array as $value) {
            $key = is_string($value) || is_int($value) || is_float($value)
                ? (string) $value
                : serialize($value);

            if (isset($seen[$key])) {
                $duplicates[$key] = $value;
            } else {
                $seen[$key] = true;
            }
        }

        return array_values($duplicates);
    }

    /**
     * Map each value through a callback.
     *
     * Callback signature: function(mixed $value, int|string $key): mixed
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): mixed $callback
     * @return array<int|string, mixed>
     */
    public static function map(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return $result;
    }

    /**
     * Map into a new associative array from key/value pairs returned by the callback.
     *
     * Callback must return a single-entry array: [newKey => newValue]
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): array<int|string, mixed> $callback
     * @return array<int|string, mixed>
     */
    public static function mapWithKeys(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $result;
    }

    /**
     * Map keys while keeping values.
     *
     * @param array<int|string, mixed> $array
     * @param callable(int|string, mixed): int|string $callback
     * @return array<int|string, mixed>
     */
    public static function mapKeys(array $array, callable $callback): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $result[$callback($key, $value)] = $value;
        }

        return $result;
    }

    /**
     * Map then collapse one level (callback should return an array per item).
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): array<int|string, mixed> $callback
     * @return list<mixed>
     */
    public static function flatMap(array $array, callable $callback): array
    {
        return self::collapse(self::map($array, $callback));
    }

    /**
     * Pluck a column of values from a list of arrays/objects.
     *
     * @param array<int|string, array<int|string, mixed>|object> $array
     * @return array<int|string, mixed>
     */
    public static function pluck(array $array, string|int $value, string|int|null $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = is_array($item)
                ? self::get($item, $value)
                : (is_object($item) ? ($item->{$value} ?? null) : null);

            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $itemKey = is_array($item)
                    ? self::get($item, $key)
                    : (is_object($item) ? ($item->{$key} ?? null) : null);

                if (is_object($itemKey)) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Reduce the array to a single value.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, mixed, int|string): mixed $callback
     */
    public static function reduce(array $array, callable $callback, mixed $initial = null): mixed
    {
        $carry = $initial;

        foreach ($array as $key => $value) {
            $carry = $callback($carry, $value, $key);
        }

        return $carry;
    }

    /**
     * Pass the array through a callback and return the result.
     *
     * @param array<int|string, mixed> $array
     * @param callable(array<int|string, mixed>): mixed $callback
     */
    public static function pipe(array $array, callable $callback): mixed
    {
        return $callback($array);
    }

    /**
     * Sort by values ascending (returns a new array, keys preserved).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function sort(array $array, int $flags = SORT_REGULAR): array
    {
        asort($array, $flags);

        return $array;
    }

    /**
     * Sort by values descending (returns a new array, keys preserved).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function sortDesc(array $array, int $flags = SORT_REGULAR): array
    {
        arsort($array, $flags);

        return $array;
    }

    /**
     * Sort by a key or callback ascending.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): mixed|string|int $callback
     * @return array<int|string, mixed>
     */
    public static function sortBy(array $array, callable|string|int $callback, int $options = SORT_REGULAR): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $results[$key] = is_callable($callback)
                ? $callback($value, $key)
                : (is_array($value) ? self::get($value, $callback) : null);
        }

        asort($results, $options);

        foreach (array_keys($results) as $key) {
            $results[$key] = $array[$key];
        }

        return $results;
    }

    /**
     * Sort by a key or callback descending.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): mixed|string|int $callback
     * @return array<int|string, mixed>
     */
    public static function sortByDesc(array $array, callable|string|int $callback, int $options = SORT_REGULAR): array
    {
        return array_reverse(self::sortBy($array, $callback, $options), true);
    }

    /**
     * Sort by keys ascending.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function sortKeys(array $array, int $flags = SORT_REGULAR): array
    {
        ksort($array, $flags);

        return $array;
    }

    /**
     * Sort by keys descending.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function sortKeysDesc(array $array, int $flags = SORT_REGULAR): array
    {
        krsort($array, $flags);

        return $array;
    }

    /**
     * Reverse the order of elements.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function reverse(array $array, bool $preserveKeys = false): array
    {
        return array_reverse($array, $preserveKeys);
    }

    /**
     * Shuffle values (returns a new array).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function shuffle(array $array): array
    {
        shuffle($array);

        return $array;
    }

    /**
     * Compute the difference of arrays (values in $array not in the others).
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function diff(array $array, array ...$arrays): array
    {
        return array_diff($array, ...$arrays);
    }

    /**
     * Compute the difference with additional index check.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function diffAssoc(array $array, array ...$arrays): array
    {
        return array_diff_assoc($array, ...$arrays);
    }

    /**
     * Compute the difference using keys.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function diffKeys(array $array, array ...$arrays): array
    {
        return array_diff_key($array, ...$arrays);
    }

    /**
     * Compute the intersection of arrays.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function intersect(array $array, array ...$arrays): array
    {
        return array_intersect($array, ...$arrays);
    }

    /**
     * Compute the intersection with additional index check.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function intersectAssoc(array $array, array ...$arrays): array
    {
        return array_intersect_assoc($array, ...$arrays);
    }

    /**
     * Compute the intersection using keys.
     *
     * @param array<int|string, mixed> $array
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function intersectKeys(array $array, array ...$arrays): array
    {
        return array_intersect_key($array, ...$arrays);
    }

    /**
     * Split an array into chunks.
     *
     * @param array<int|string, mixed> $array
     * @return list<array<int|string, mixed>>
     */
    public static function chunk(array $array, int $size, bool $preserveKeys = false): array
    {
        if ($size < 1) {
            throw new InvalidArgumentException('chunk() size must be at least 1.');
        }

        return array_chunk($array, $size, $preserveKeys);
    }

    /**
     * Extract a slice of the array.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function slice(array $array, int $offset, ?int $length = null, bool $preserveKeys = false): array
    {
        return array_slice($array, $offset, $length, $preserveKeys);
    }

    /**
     * Take the first $limit items (negative $limit takes from the end).
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function take(array $array, int $limit): array
    {
        if ($limit < 0) {
            return self::slice($array, $limit, abs($limit), true);
        }

        return self::slice($array, 0, $limit, true);
    }

    /**
     * Skip the first $count items.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function skip(array $array, int $count): array
    {
        return self::slice($array, max(0, $count), null, true);
    }

    /**
     * Partition into [passed, failed] using a callback.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     * @return array{0: array<int|string, mixed>, 1: array<int|string, mixed>}
     */
    public static function partition(array $array, callable $callback): array
    {
        $passed = [];
        $failed = [];

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                $passed[$key] = $value;
            } else {
                $failed[$key] = $value;
            }
        }

        return [$passed, $failed];
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param array<int|string, mixed> $array
     * @return list<mixed>
     */
    public static function flatten(array $array, int|float $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            if (! is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                array_push($result, ...array_values($item));
            } else {
                array_push($result, ...self::flatten($item, $depth - 1));
            }
        }

        return $result;
    }

    /**
     * Collapse an array of arrays into a single array (one level).
     *
     * @param array<int|string, mixed> $array
     * @return list<mixed>
     */
    public static function collapse(array $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (! is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }

    /**
     * Flatten a multi-dimensional associative array with "dot" keys.
     *
     * @param array<int|string, mixed> $array
     * @return array<string, mixed>
     */
    public static function dot(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && $value !== []) {
                $results = array_merge($results, self::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    /**
     * Expand a dotted array into a multi-dimensional associative array.
     *
     * @param array<string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function undot(array $array): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $results = self::setDot($results, (string) $key, $value);
        }

        return $results;
    }

    /**
     * Zip multiple arrays into tuples.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return list<list<mixed>>
     */
    public static function zip(array ...$arrays): array
    {
        if ($arrays === []) {
            return [];
        }

        $arrays = array_map('array_values', $arrays);
        $max = max(array_map('count', $arrays));
        $result = [];

        for ($i = 0; $i < $max; $i++) {
            $tuple = [];

            foreach ($arrays as $array) {
                $tuple[] = $array[$i] ?? null;
            }

            $result[] = $tuple;
        }

        return $result;
    }

    /**
     * Cross join the given arrays, returning all possible permutations.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return list<list<mixed>>
     */
    public static function crossJoin(array ...$arrays): array
    {
        $results = [[]];

        foreach ($arrays as $index => $array) {
            $append = [];

            foreach ($results as $product) {
                foreach ($array as $item) {
                    $product[$index] = $item;
                    $append[] = $product;
                }
            }

            $results = $append;
        }

        return $results;
    }

    /**
     * Pad an array to the specified length with a value.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function pad(array $array, int $size, mixed $value): array
    {
        return array_pad($array, $size, $value);
    }

    /**
     * Count all elements.
     *
     * @param array<int|string, mixed> $array
     */
    public static function count(array $array): int
    {
        return count($array);
    }

    /**
     * Determine whether the array has no elements.
     *
     * @param array<int|string, mixed> $array
     */
    public static function isEmpty(array $array): bool
    {
        return $array === [];
    }

    /**
     * Determine whether the array has at least one element.
     *
     * @param array<int|string, mixed> $array
     */
    public static function isNotEmpty(array $array): bool
    {
        return $array !== [];
    }

    /**
     * Sum numeric values.
     *
     * @param array<int|string, int|float|string> $array
     */
    public static function sum(array $array): int|float
    {
        return array_sum($array);
    }

    /**
     * Average of numeric values, or null for an empty array.
     *
     * @param array<int|string, int|float|string> $array
     */
    public static function average(array $array): int|float|null
    {
        if ($array === []) {
            return null;
        }

        return array_sum($array) / count($array);
    }

    /**
     * Alias of average().
     *
     * @param array<int|string, int|float|string> $array
     */
    public static function avg(array $array): int|float|null
    {
        return self::average($array);
    }

    /**
     * Lowest value, or null for an empty array.
     *
     * @param array<int|string, mixed> $array
     */
    public static function min(array $array): mixed
    {
        return $array === [] ? null : min($array);
    }

    /**
     * Highest value, or null for an empty array.
     *
     * @param array<int|string, mixed> $array
     */
    public static function max(array $array): mixed
    {
        return $array === [] ? null : max($array);
    }

    /**
     * Product of numeric values.
     *
     * @param array<int|string, int|float|string> $array
     */
    public static function product(array $array): int|float
    {
        return array_product($array);
    }

    /**
     * Determine whether every element passes the truth test.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     */
    public static function every(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if (! $callback($value, $key)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine whether at least one element passes the truth test.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     */
    public static function some(array $array, callable $callback): bool
    {
        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alias of some().
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): bool $callback
     */
    public static function any(array $array, callable $callback): bool
    {
        return self::some($array, $callback);
    }

    /**
     * Get one random value, or a list of $number random values.
     *
     * @param array<int|string, mixed> $array
     * @return mixed|list<mixed>
     */
    public static function random(array $array, ?int $number = null): mixed
    {
        if ($array === []) {
            throw new InvalidArgumentException('random() requires a non-empty array.');
        }

        if ($number === null) {
            return $array[array_rand($array)];
        }

        if ($number < 1) {
            throw new InvalidArgumentException('random() number must be at least 1.');
        }

        $number = min($number, count($array));
        $keys = (array) array_rand($array, $number);
        $results = [];

        foreach ($keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }

    /**
     * Key the array by the given key or callback.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): int|string|string|int $keyBy
     * @return array<int|string, mixed>
     */
    public static function keyBy(array $array, callable|string|int $keyBy): array
    {
        $results = [];

        foreach ($array as $key => $item) {
            $resolved = is_callable($keyBy)
                ? $keyBy($item, $key)
                : (is_array($item) ? self::get($item, $keyBy) : null);

            $results[$resolved] = $item;
        }

        return $results;
    }

    /**
     * Group items by the given key or callback.
     *
     * @param array<int|string, mixed> $array
     * @param callable(mixed, int|string): int|string|string|int $groupBy
     * @return array<int|string, list<mixed>>
     */
    public static function groupBy(array $array, callable|string|int $groupBy): array
    {
        $results = [];

        foreach ($array as $key => $item) {
            $resolved = is_callable($groupBy)
                ? $groupBy($item, $key)
                : (is_array($item) ? self::get($item, $groupBy) : null);

            $results[$resolved][] = $item;
        }

        return $results;
    }

    /**
     * Flip keys and values.
     *
     * @param array<int|string, int|string> $array
     * @return array<int|string, int|string>
     */
    public static function flip(array $array): array
    {
        return array_flip($array);
    }

    /**
     * Execute a callback with the array, then return the original array.
     *
     * @param array<int|string, mixed> $array
     * @param callable(array<int|string, mixed>): void $callback
     * @return array<int|string, mixed>
     */
    public static function tap(array $array, callable $callback): array
    {
        $callback($array);

        return $array;
    }

    /**
     * Apply $callback when $condition is true (or callable returns true).
     *
     * @param array<int|string, mixed> $array
     * @param bool|(callable(array<int|string, mixed>): bool) $condition
     * @param callable(array<int|string, mixed>): array<int|string, mixed> $callback
     * @param (callable(array<int|string, mixed>): array<int|string, mixed>)|null $default
     * @return array<int|string, mixed>
     */
    public static function when(array $array, bool|callable $condition, callable $callback, ?callable $default = null): array
    {
        $passes = is_callable($condition) ? $condition($array) : $condition;

        if ($passes) {
            return $callback($array);
        }

        return $default ? $default($array) : $array;
    }

    /**
     * Apply $callback when $condition is false.
     *
     * @param array<int|string, mixed> $array
     * @param bool|(callable(array<int|string, mixed>): bool) $condition
     * @param callable(array<int|string, mixed>): array<int|string, mixed> $callback
     * @param (callable(array<int|string, mixed>): array<int|string, mixed>)|null $default
     * @return array<int|string, mixed>
     */
    public static function unless(array $array, bool|callable $condition, callable $callback, ?callable $default = null): array
    {
        $passes = is_callable($condition) ? $condition($array) : $condition;

        return self::when($array, ! $passes, $callback, $default);
    }

    /**
     * Ensure the value is an array; convert Traversable, wrap scalars, empty for null.
     *
     * @return array<int|string, mixed>
     */
    public static function ensure(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof Traversable) {
            return iterator_to_array($value);
        }

        return self::wrap($value);
    }
}
