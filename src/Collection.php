<?php

namespace Benammi\ArrayAIO;

use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * A fluent, Laravel-style collection wrapper around PHP arrays.
 *
 * Almost every method returns a new Collection instance (or a scalar when that
 * is the natural result), so you can chain transforms safely without mutating
 * the original data.
 *
 * @implements ArrayAccess<int|string, mixed>
 * @implements IteratorAggregate<int|string, mixed>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * Methods that support higher-order messages (e.g. $collection->map->name).
     *
     * @var list<string>
     */
    protected static array $proxies = [
        'average',
        'avg',
        'contains',
        'each',
        'every',
        'filter',
        'first',
        'flatMap',
        'groupBy',
        'keyBy',
        'map',
        'max',
        'min',
        'partition',
        'reject',
        'some',
        'sortBy',
        'sortByDesc',
        'sum',
        'unique',
    ];

    /**
     * @var array<int|string, mixed>
     */
    protected array $items = [];

    /**
     * @param  mixed  $items
     */
    public function __construct(mixed $items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Proxy property access to higher-order collection methods.
     */
    public function __get(string $key): mixed
    {
        if (! in_array($key, static::$proxies, true)) {
            throw new InvalidArgumentException("Property [{$key}] does not exist on this collection instance.");
        }

        return new HigherOrderCollectionProxy($this, $key);
    }

    /**
     * Create a new collection instance.
     *
     * @param  mixed  $items
     */
    public static function make(mixed $items = []): static
    {
        return new static($items);
    }

    /**
     * Create a collection with the given range.
     */
    public static function range(int|float|string $start, int|float|string $end, int|float $step = 1): static
    {
        return new static(ArrayAIO::range($start, $end, $step));
    }

    /**
     * Create a collection by invoking the callback a given number of times.
     *
     * @param  callable(int): mixed|null  $callback
     */
    public static function times(int $number, ?callable $callback = null): static
    {
        if ($number < 1) {
            return new static([]);
        }

        return static::range(1, $number)->map(
            $callback ?? static fn (int $number): int => $number
        )->values();
    }

    /**
     * Wrap the given value in a collection if necessary.
     *
     * @param  mixed  $value
     */
    public static function wrap(mixed $value): static
    {
        if ($value instanceof self) {
            return new static($value->all());
        }

        return new static(ArrayAIO::wrap($value));
    }

    /**
     * Get the underlying items as a plain array when possible.
     *
     * @param  mixed  $value
     * @return array<int|string, mixed>|mixed
     */
    public static function unwrap(mixed $value): mixed
    {
        return $value instanceof self ? $value->all() : $value;
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array<int|string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(): array
    {
        return array_map(
            static fn (mixed $value): mixed => $value instanceof self ? $value->toArray() : $value,
            $this->items
        );
    }

    /**
     * Convert the collection to JSON.
     *
     * @param  int  $options
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options) ?: '[]';
    }

    /**
     * @return array<int|string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<int|string, mixed>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    /**
     * Determine if the collection is empty.
     */
    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    /**
     * Determine if the collection is not empty.
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Get a value by key.
     */
    public function get(int|string $key, mixed $default = null): mixed
    {
        return ArrayAIO::get($this->items, $key, \value($default));
    }

    /**
     * Get an item using "dot" notation.
     */
    public function getDot(string $path, mixed $default = null): mixed
    {
        return ArrayAIO::getDot($this->items, $path, \value($default));
    }

    /**
     * Determine if an item exists by key.
     *
     * @param  int|string|array<int, int|string>  $key
     */
    public function has(int|string|array $key): bool
    {
        return ArrayAIO::has($this->items, $key);
    }

    /**
     * Determine if any of the keys exist.
     *
     * @param  array<int, int|string>  $keys
     */
    public function hasAny(array $keys): bool
    {
        return ArrayAIO::hasAny($this->items, $keys);
    }

    /**
     * Put a value in the collection.
     */
    public function put(int|string $key, mixed $value): static
    {
        return new static(ArrayAIO::set($this->items, $key, $value));
    }

    /**
     * Remove items by key.
     *
     * @param  int|string|array<int, int|string>  $keys
     */
    public function forget(int|string|array $keys): static
    {
        return new static(ArrayAIO::forget($this->items, $keys));
    }

    /**
     * Get and remove an item from the collection.
     */
    public function pull(int|string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->items = ArrayAIO::forget($this->items, $key);

        return $value;
    }

    /**
     * Push one or more items onto the end.
     */
    public function push(mixed ...$values): static
    {
        return new static(ArrayAIO::push($this->items, ...$values));
    }

    /**
     * Push an item onto the beginning.
     */
    public function prepend(mixed $value, int|string|null $key = null): static
    {
        if ($key === null) {
            return new static(ArrayAIO::prepend($this->items, $value));
        }

        return new static([$key => $value] + $this->items);
    }

    /**
     * Alias of prepend without a key.
     */
    public function unshift(mixed $value, int|string|null $key = null): static
    {
        return $this->prepend($value, $key);
    }

    /**
     * Get and remove the last item(s).
     *
     * @return mixed|static
     */
    public function pop(?int $count = null): mixed
    {
        if ($count === null) {
            $items = $this->items;
            $value = array_pop($items);
            $this->items = $items;

            return $value;
        }

        if ($count <= 0) {
            return new static([]);
        }

        $results = [];

        for ($i = 0; $i < $count && $this->items !== []; $i++) {
            $results[] = $this->pop();
        }

        return new static(array_reverse($results));
    }

    /**
     * Get and remove the first item(s).
     *
     * @return mixed|static
     */
    public function shift(?int $count = null): mixed
    {
        if ($count === null) {
            $items = $this->items;
            $value = array_shift($items);
            $this->items = $items;

            return $value;
        }

        if ($count <= 0) {
            return new static([]);
        }

        $results = [];

        for ($i = 0; $i < $count && $this->items !== []; $i++) {
            $results[] = $this->shift();
        }

        return new static($results);
    }

    /**
     * Concatenate values onto the end (re-indexed).
     *
     * @param  mixed  $source
     */
    public function concat(mixed $source): static
    {
        return new static(ArrayAIO::concat($this->items, $this->getArrayableItems($source)));
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed  $items
     */
    public function merge(mixed $items): static
    {
        return new static(ArrayAIO::merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Recursively merge the collection with the given items.
     *
     * @param  mixed  $items
     */
    public function mergeRecursive(mixed $items): static
    {
        return new static(ArrayAIO::mergeRecursive($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Union the collection with the given items (existing keys win).
     *
     * @param  mixed  $items
     */
    public function union(mixed $items): static
    {
        return new static($this->items + $this->getArrayableItems($items));
    }

    /**
     * Replace items in the collection.
     *
     * @param  mixed  $items
     */
    public function replace(mixed $items): static
    {
        return new static(ArrayAIO::replace($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Recursively replace items in the collection.
     *
     * @param  mixed  $items
     */
    public function replaceRecursive(mixed $items): static
    {
        return new static(ArrayAIO::replaceRecursive($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the keys of the collection items.
     */
    public function keys(): static
    {
        return new static(ArrayAIO::keys($this->items));
    }

    /**
     * Reset the keys on the underlying array.
     */
    public function values(): static
    {
        return new static(ArrayAIO::values($this->items));
    }

    /**
     * Get a subset of items for the given keys.
     *
     * @param  array<int, int|string>|string  $keys
     */
    public function only(array|string $keys): static
    {
        return new static(ArrayAIO::only($this->items, ArrayAIO::wrap($keys)));
    }

    /**
     * Alias of only().
     *
     * @param  array<int, int|string>|string|null  $keys
     */
    public function select(array|string|null $keys): static
    {
        if ($keys === null) {
            return new static($this->items);
        }

        return $this->only($keys);
    }

    /**
     * Get all items except for the given keys.
     *
     * @param  array<int, int|string>|string  $keys
     */
    public function except(array|string $keys): static
    {
        return new static(ArrayAIO::except($this->items, ArrayAIO::wrap($keys)));
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable(mixed, int|string): mixed  $callback
     */
    public function map(callable $callback): static
    {
        return new static(ArrayAIO::map($this->items, $callback));
    }

    /**
     * Run an associative map over each of the items.
     *
     * @param  callable(mixed, int|string): array<int|string, mixed>  $callback
     */
    public function mapWithKeys(callable $callback): static
    {
        return new static(ArrayAIO::mapWithKeys($this->items, $callback));
    }

    /**
     * Map items into instances of the given class.
     *
     * @param  class-string  $class
     */
    public function mapInto(string $class): static
    {
        return $this->map(static fn (mixed $value): object => new $class($value));
    }

    /**
     * Map a collection then collapse one level.
     *
     * @param  callable(mixed, int|string): mixed  $callback
     */
    public function flatMap(callable $callback): static
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Map items to groups.
     *
     * @param  callable(mixed, int|string): array<int|string, mixed>  $callback
     */
    public function mapToGroups(callable $callback): static
    {
        $groups = [];

        foreach ($this->items as $key => $item) {
            foreach ($callback($item, $key) as $groupKey => $groupValue) {
                $groups[$groupKey][] = $groupValue;
            }
        }

        return (new static($groups))->map(static fn (array $items): self => new static($items));
    }

    /**
     * Map items using a callback that receives spread values for array items.
     *
     * @param  callable(mixed...): mixed  $callback
     */
    public function mapSpread(callable $callback): static
    {
        return $this->map(static function (mixed $chunk) use ($callback): mixed {
            $chunk = ArrayAIO::wrap($chunk);

            return $callback(...array_values($chunk));
        });
    }

    /**
     * Transform items in place (mutates this collection) and return $this.
     *
     * @param  callable(mixed, int|string): mixed  $callback
     */
    public function transform(callable $callback): static
    {
        $this->items = ArrayAIO::map($this->items, $callback);

        return $this;
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable(mixed, int|string): mixed  $callback
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Execute a callback over each nested chunk with spread arguments.
     *
     * @param  callable(mixed...): mixed  $callback
     */
    public function eachSpread(callable $callback): static
    {
        return $this->each(static function (mixed $chunk) use ($callback): mixed {
            $chunk = ArrayAIO::wrap($chunk);

            return $callback(...array_values($chunk));
        });
    }

    /**
     * Filter items by a truth test.
     *
     * @param  (callable(mixed, int|string): bool)|null  $callback
     */
    public function filter(?callable $callback = null): static
    {
        return new static(ArrayAIO::filter($this->items, $callback));
    }

    /**
     * Filter items that fail the truth test.
     *
     * @param  callable(mixed, int|string): bool  $callback
     */
    public function reject(callable $callback): static
    {
        return new static(ArrayAIO::reject($this->items, $callback));
    }

    /**
     * Filter items by key comparison (supports operators).
     */
    public function where(callable|string|int $key, mixed $operator = null, mixed $value = null): static
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Strict where (===).
     */
    public function whereStrict(string|int $key, mixed $value): static
    {
        return $this->where($key, '===', $value);
    }

    /**
     * Filter items where the value is null.
     */
    public function whereNull(string|int|null $key = null): static
    {
        if ($key === null) {
            return $this->filter(static fn (mixed $value): bool => $value === null);
        }

        return $this->filter(
            static fn (mixed $item): bool => \data_get($item, (string) $key) === null
        );
    }

    /**
     * Filter items where the value is not null.
     */
    public function whereNotNull(string|int|null $key = null): static
    {
        if ($key === null) {
            return $this->filter(static fn (mixed $value): bool => $value !== null);
        }

        return $this->filter(
            static fn (mixed $item): bool => \data_get($item, (string) $key) !== null
        );
    }

    /**
     * Filter items where key is in values.
     *
     * @param  array<int, mixed>  $values
     */
    public function whereIn(string|int $key, array $values, bool $strict = false): static
    {
        $values = array_values($values);

        return $this->filter(
            static fn (mixed $item): bool => in_array(\data_get($item, (string) $key), $values, $strict)
        );
    }

    /**
     * Strict whereIn.
     *
     * @param  array<int, mixed>  $values
     */
    public function whereInStrict(string|int $key, array $values): static
    {
        return $this->whereIn($key, $values, true);
    }

    /**
     * Filter items where key is not in values.
     *
     * @param  array<int, mixed>  $values
     */
    public function whereNotIn(string|int $key, array $values, bool $strict = false): static
    {
        $values = array_values($values);

        return $this->filter(
            static fn (mixed $item): bool => ! in_array(\data_get($item, (string) $key), $values, $strict)
        );
    }

    /**
     * Strict whereNotIn.
     *
     * @param  array<int, mixed>  $values
     */
    public function whereNotInStrict(string|int $key, array $values): static
    {
        return $this->whereNotIn($key, $values, true);
    }

    /**
     * Filter items where key is between two values (inclusive).
     */
    public function whereBetween(string|int $key, array $values): static
    {
        return $this->filter(static function (mixed $item) use ($key, $values): bool {
            $itemValue = \data_get($item, (string) $key);

            return $itemValue >= $values[0] && $itemValue <= $values[1];
        });
    }

    /**
     * Filter items where key is not between two values.
     */
    public function whereNotBetween(string|int $key, array $values): static
    {
        return $this->filter(static function (mixed $item) use ($key, $values): bool {
            $itemValue = \data_get($item, (string) $key);

            return $itemValue < $values[0] || $itemValue > $values[1];
        });
    }

    /**
     * Filter items that are instances of the given class.
     *
     * @param  class-string  $type
     */
    public function whereInstanceOf(string $type): static
    {
        return $this->filter(static fn (mixed $value): bool => $value instanceof $type);
    }

    /**
     * Get the first item, optionally matching a callback.
     *
     * @param  (callable(mixed, int|string): bool)|null  $callback
     */
    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        return ArrayAIO::first($this->items, $callback, \value($default));
    }

    /**
     * Get the first item or throw.
     *
     * @param  (callable(mixed, int|string): bool)|null  $callback
     */
    public function firstOrFail(?callable $callback = null): mixed
    {
        $value = $this->first($callback, new ItemNotFoundException('Item not found.'));

        if ($value instanceof ItemNotFoundException) {
            throw $value;
        }

        return $value;
    }

    /**
     * Get the first item by key / operator / value.
     */
    public function firstWhere(callable|string|int $key, mixed $operator = null, mixed $value = null): mixed
    {
        return $this->first($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get the last item, optionally matching a callback.
     *
     * @param  (callable(mixed, int|string): bool)|null  $callback
     */
    public function last(?callable $callback = null, mixed $default = null): mixed
    {
        return ArrayAIO::last($this->items, $callback, \value($default));
    }

    /**
     * Get the item at a given offset among values.
     */
    public function nth(int $step, int $offset = 0): static
    {
        $items = array_values($this->items);
        $results = [];
        $position = 0;

        foreach ($items as $item) {
            if ($position % $step === $offset) {
                $results[] = $item;
            }
            $position++;
        }

        return new static($results);
    }

    /**
     * Get a value from the first matching item by key.
     */
    public function value(string|int $key, mixed $default = null): mixed
    {
        if ($result = $this->firstWhere($key, '!=', null)) {
            return \data_get($result, (string) $key, \value($default));
        }

        return \value($default);
    }

    /**
     * Determine if an item exists in the collection.
     */
    public function contains(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                return ArrayAIO::some($this->items, $key);
            }

            return ArrayAIO::contains($this->items, $key);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Strict contains.
     */
    public function containsStrict(mixed $key, mixed $value = null): bool
    {
        if (func_num_args() === 2) {
            return $this->contains(static fn (mixed $item): bool => \data_get($item, (string) $key) === $value);
        }

        if ($this->useAsCallable($key)) {
            return ArrayAIO::some($this->items, $key);
        }

        return ArrayAIO::contains($this->items, $key, true);
    }

    /**
     * Determine if the collection contains a single item.
     */
    public function containsOneItem(): bool
    {
        return $this->count() === 1;
    }

    /**
     * Determine if an item is not contained.
     */
    public function doesntContain(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        return ! $this->contains(...func_get_args());
    }

    /**
     * Alias of contains for truth tests.
     */
    public function some(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        return $this->contains(...func_get_args());
    }

    /**
     * Determine if every item passes a truth test.
     *
     * @param  callable(mixed, int|string): bool  $callback
     */
    public function every(callable $callback): bool
    {
        return ArrayAIO::every($this->items, $callback);
    }

    /**
     * Search for a value and return its key.
     */
    public function search(mixed $value, bool $strict = false): int|string|false
    {
        if (! $this->useAsCallable($value)) {
            return ArrayAIO::search($this->items, $value, $strict);
        }

        foreach ($this->items as $key => $item) {
            if ($value($item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get unique items.
     *
     * @param  (callable(mixed, int|string): mixed)|string|null  $key
     */
    public function unique(callable|string|null $key = null, bool $strict = false): static
    {
        if ($key === null && $strict === false) {
            return new static(ArrayAIO::unique($this->items, SORT_REGULAR));
        }

        $callback = $this->valueRetriever($key);
        $exists = [];

        return $this->reject(function (mixed $item, int|string $itemKey) use ($callback, &$exists, $strict): bool {
            $id = $callback($item, $itemKey);

            if (in_array($id, $exists, $strict)) {
                return true;
            }

            $exists[] = $id;

            return false;
        });
    }

    /**
     * Get unique items using strict comparison.
     *
     * @param  (callable(mixed, int|string): mixed)|string|null  $key
     */
    public function uniqueStrict(callable|string|null $key = null): static
    {
        return $this->unique($key, true);
    }

    /**
     * Get duplicate values.
     *
     * @param  (callable(mixed, int|string): mixed)|string|null  $callback
     */
    public function duplicates(callable|string|null $callback = null, bool $strict = false): static
    {
        $callback = $this->valueRetriever($callback);
        $items = [];
        $duplicates = [];

        foreach ($this->items as $key => $item) {
            $value = $callback($item, $key);

            if (in_array($value, $items, $strict)) {
                $duplicates[$key] = $value;
            } else {
                $items[$key] = $value;
            }
        }

        return new static($duplicates);
    }

    /**
     * Get duplicate values using strict comparison.
     *
     * @param  (callable(mixed, int|string): mixed)|string|null  $callback
     */
    public function duplicatesStrict(callable|string|null $callback = null): static
    {
        return $this->duplicates($callback, true);
    }

    /**
     * Pluck values.
     */
    public function pluck(string|int|array $value, string|int|null $key = null): static
    {
        if (is_array($value)) {
            $value = implode('.', $value);
        }

        return new static(ArrayAIO::pluck($this->items, $value, $key));
    }

    /**
     * Key by a given key or callback.
     *
     * @param  callable(mixed, int|string): mixed|string|int  $keyBy
     */
    public function keyBy(callable|string|int $keyBy): static
    {
        return new static(ArrayAIO::keyBy($this->items, $keyBy));
    }

    /**
     * Group by a given key or callback.
     *
     * @param  callable(mixed, int|string): mixed|string|int|array  $groupBy
     */
    public function groupBy(callable|string|int|array $groupBy, bool $preserveKeys = false): static
    {
        if (is_array($groupBy) && ! $this->useAsCallable($groupBy)) {
            $nextGroups = $groupBy;
            $groupBy = array_shift($nextGroups);
        } else {
            $nextGroups = null;
        }

        $groupBy = $this->valueRetriever($groupBy);
        $results = [];

        foreach ($this->items as $key => $value) {
            $groupKeys = ArrayAIO::wrap($groupBy($value, $key));

            foreach ($groupKeys as $groupKey) {
                if (is_bool($groupKey)) {
                    $groupKey = (int) $groupKey;
                }

                if (! array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static();
                }

                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }

        $result = new static($results);

        if (! empty($nextGroups)) {
            return $result->map(static fn (self $group): self => $group->groupBy($nextGroups, $preserveKeys));
        }

        return $result;
    }

    /**
     * Count occurrences by key/callback.
     *
     * @param  (callable(mixed, int|string): mixed)|string|null  $countBy
     */
    public function countBy(callable|string|null $countBy = null): static
    {
        return new static(
            ArrayAIO::map(
                ArrayAIO::groupBy($this->items, $countBy ?? static fn (mixed $value): mixed => $value),
                static fn (array $group): int => count($group)
            )
        );
    }

    /**
     * Sort by values ascending.
     */
    public function sort(int|callable|null $callback = null): static
    {
        if (is_callable($callback)) {
            $items = $this->items;
            uasort($items, $callback);

            return new static($items);
        }

        return new static(ArrayAIO::sort($this->items, is_int($callback) ? $callback : SORT_REGULAR));
    }

    /**
     * Sort by values descending.
     */
    public function sortDesc(int $options = SORT_REGULAR): static
    {
        return new static(ArrayAIO::sortDesc($this->items, $options));
    }

    /**
     * Sort by a key or callback.
     *
     * @param  callable(mixed, int|string): mixed|string|array|null  $callback
     */
    public function sortBy(callable|string|array|null $callback, int $options = SORT_REGULAR, bool $descending = false): static
    {
        if (is_array($callback) && ! $this->useAsCallable($callback)) {
            return $this->sortByMany($callback, $options);
        }

        $results = ArrayAIO::sortBy($this->items, $callback ?? static fn (mixed $value): mixed => $value, $options);

        return new static($descending ? array_reverse($results, true) : $results);
    }

    /**
     * Sort by a key or callback descending.
     *
     * @param  callable(mixed, int|string): mixed|string|array|null  $callback
     */
    public function sortByDesc(callable|string|array|null $callback, int $options = SORT_REGULAR): static
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sort by keys ascending.
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): static
    {
        return new static(
            $descending
                ? ArrayAIO::sortKeysDesc($this->items, $options)
                : ArrayAIO::sortKeys($this->items, $options)
        );
    }

    /**
     * Sort by keys descending.
     */
    public function sortKeysDesc(int $options = SORT_REGULAR): static
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Sort keys using a callback.
     *
     * @param  callable(int|string, int|string): int  $callback
     */
    public function sortKeysUsing(callable $callback): static
    {
        $items = $this->items;
        uksort($items, $callback);

        return new static($items);
    }

    /**
     * Reverse item order.
     */
    public function reverse(): static
    {
        return new static(ArrayAIO::reverse($this->items, true));
    }

    /**
     * Shuffle the items.
     */
    public function shuffle(?int $seed = null): static
    {
        if ($seed !== null) {
            mt_srand($seed);
        }

        return new static(ArrayAIO::shuffle($this->items));
    }

    /**
     * Diff the collection against given items.
     *
     * @param  mixed  $items
     */
    public function diff(mixed $items): static
    {
        return new static(ArrayAIO::diff($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Diff using keys and values.
     *
     * @param  mixed  $items
     */
    public function diffAssoc(mixed $items): static
    {
        return new static(ArrayAIO::diffAssoc($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Diff using keys.
     *
     * @param  mixed  $items
     */
    public function diffKeys(mixed $items): static
    {
        return new static(ArrayAIO::diffKeys($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Intersect with the given items.
     *
     * @param  mixed  $items
     */
    public function intersect(mixed $items): static
    {
        return new static(ArrayAIO::intersect($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Intersect with keys and values.
     *
     * @param  mixed  $items
     */
    public function intersectAssoc(mixed $items): static
    {
        return new static(ArrayAIO::intersectAssoc($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Intersect using keys.
     *
     * @param  mixed  $items
     */
    public function intersectByKeys(mixed $items): static
    {
        return new static(ArrayAIO::intersectKeys($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Chunk into smaller collections.
     */
    public function chunk(int $size): static
    {
        if ($size <= 0) {
            return new static([]);
        }

        return new static(
            array_map(
                static fn (array $chunk): self => new static($chunk),
                ArrayAIO::chunk($this->items, $size, true)
            )
        );
    }

    /**
     * Chunk while the callback returns true.
     *
     * @param  callable(mixed, int|string, static): bool  $callback
     */
    public function chunkWhile(callable $callback): static
    {
        $chunks = [];
        $chunk = new static();

        foreach ($this->items as $key => $value) {
            if ($chunk->isNotEmpty() && ! $callback($value, $key, $chunk)) {
                $chunks[] = $chunk;
                $chunk = new static();
            }

            $chunk[$key] = $value;
        }

        if ($chunk->isNotEmpty()) {
            $chunks[] = $chunk;
        }

        return new static($chunks);
    }

    /**
     * Split into a given number of groups.
     */
    public function split(int $numberOfGroups): static
    {
        if ($this->isEmpty()) {
            return new static([]);
        }

        $groups = new static();
        $groupSize = (int) ceil($this->count() / $numberOfGroups);
        $current = new static();

        foreach ($this->items as $key => $value) {
            $current[$key] = $value;

            if ($current->count() === $groupSize) {
                $groups->push($current);
                $current = new static();
            }
        }

        if ($current->isNotEmpty()) {
            $groups->push($current);
        }

        return $groups;
    }

    /**
     * Split into groups of equal size, padding leftovers into earlier groups.
     */
    public function splitIn(int $numberOfGroups): static
    {
        return $this->chunk((int) ceil($this->count() / max(1, $numberOfGroups)));
    }

    /**
     * Slice the underlying array.
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(ArrayAIO::slice($this->items, $offset, $length, true));
    }

    /**
     * Take the first or last {$limit} items.
     */
    public function take(int $limit): static
    {
        return new static(ArrayAIO::take($this->items, $limit));
    }

    /**
     * Take items until the callback returns true.
     *
     * @param  callable(mixed, int|string): bool|mixed  $callback
     */
    public function takeUntil(mixed $callback): static
    {
        if (! $this->useAsCallable($callback)) {
            $callback = static fn (mixed $item): bool => $item === $callback;
        }

        $results = [];

        foreach ($this->items as $key => $item) {
            if ($callback($item, $key)) {
                break;
            }

            $results[$key] = $item;
        }

        return new static($results);
    }

    /**
     * Take items while the callback returns true.
     *
     * @param  callable(mixed, int|string): bool|mixed  $callback
     */
    public function takeWhile(mixed $callback): static
    {
        if (! $this->useAsCallable($callback)) {
            $callback = static fn (mixed $item): bool => $item === $callback;
        }

        $results = [];

        foreach ($this->items as $key => $item) {
            if (! $callback($item, $key)) {
                break;
            }

            $results[$key] = $item;
        }

        return new static($results);
    }

    /**
     * Skip the first {$count} items.
     */
    public function skip(int $count): static
    {
        return new static(ArrayAIO::skip($this->items, $count));
    }

    /**
     * Skip items until the callback returns true.
     *
     * @param  callable(mixed, int|string): bool|mixed  $callback
     */
    public function skipUntil(mixed $callback): static
    {
        if (! $this->useAsCallable($callback)) {
            $callback = static fn (mixed $item): bool => $item === $callback;
        }

        $found = false;
        $results = [];

        foreach ($this->items as $key => $item) {
            if (! $found && $callback($item, $key)) {
                $found = true;
            }

            if ($found) {
                $results[$key] = $item;
            }
        }

        return new static($results);
    }

    /**
     * Skip items while the callback returns true.
     *
     * @param  callable(mixed, int|string): bool|mixed  $callback
     */
    public function skipWhile(mixed $callback): static
    {
        if (! $this->useAsCallable($callback)) {
            $callback = static fn (mixed $item): bool => $item === $callback;
        }

        $results = [];
        $skipping = true;

        foreach ($this->items as $key => $item) {
            if ($skipping && $callback($item, $key)) {
                continue;
            }

            $skipping = false;
            $results[$key] = $item;
        }

        return new static($results);
    }

    /**
     * Paginate using page number and size.
     */
    public function forPage(int $page, int $perPage): static
    {
        $offset = max(0, ($page - 1) * $perPage);

        return $this->slice($offset, $perPage);
    }

    /**
     * Create a sliding window over items.
     */
    public function sliding(int $size = 2, int $step = 1): static
    {
        $chunks = (int) floor(($this->count() - $size) / $step) + 1;

        if ($chunks <= 0) {
            return new static([]);
        }

        return static::times($chunks)
            ->map(fn (int $number): self => $this->slice(($number - 1) * $step, $size))
            ->values();
    }

    /**
     * Splice a portion of the underlying items (mutates and returns removed portion).
     *
     * @param  array<int, mixed>  $replacement
     */
    public function splice(int $offset, ?int $length = null, array $replacement = []): static
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length ?? count($this->items), $replacement));
    }

    /**
     * Collapse a collection of arrays into a single flat collection.
     */
    public function collapse(): static
    {
        return new static(ArrayAIO::collapse($this->items));
    }

    /**
     * Collapse while preserving keys.
     */
    public function collapseWithKeys(): static
    {
        $results = [];

        foreach ($this->items as $values) {
            if ($values instanceof self) {
                $values = $values->all();
            }

            if (! is_array($values)) {
                continue;
            }

            $results += $values;
        }

        return new static($results);
    }

    /**
     * Flatten a multi-dimensional collection.
     */
    public function flatten(int|float $depth = INF): static
    {
        return new static(ArrayAIO::flatten($this->items, $depth));
    }

    /**
     * Flatten into dotted keys.
     */
    public function dot(): static
    {
        return new static(ArrayAIO::dot($this->items));
    }

    /**
     * Expand dotted keys.
     */
    public function undot(): static
    {
        return new static(ArrayAIO::undot($this->items));
    }

    /**
     * Flip keys and values.
     */
    public function flip(): static
    {
        return new static(ArrayAIO::flip($this->items));
    }

    /**
     * Pad to size.
     */
    public function pad(int $size, mixed $value): static
    {
        return new static(ArrayAIO::pad($this->items, $size, $value));
    }

    /**
     * Zip collections together.
     *
     * @param  mixed  ...$items
     */
    public function zip(mixed ...$items): static
    {
        $arrays = array_map(fn (mixed $item): array => $this->getArrayableItems($item), $items);

        return new static(
            array_map(
                static fn (array $tuple): self => new static($tuple),
                ArrayAIO::zip($this->items, ...$arrays)
            )
        );
    }

    /**
     * Cross join with the given lists.
     *
     * @param  mixed  ...$lists
     */
    public function crossJoin(mixed ...$lists): static
    {
        $arrays = array_map(fn (mixed $list): array => $this->getArrayableItems($list), $lists);

        return new static(ArrayAIO::crossJoin($this->items, ...$arrays));
    }

    /**
     * Combine the values with the given keys.
     *
     * @param  mixed  $values
     */
    public function combine(mixed $values): static
    {
        return new static(ArrayAIO::combine($this->all(), $this->getArrayableItems($values)));
    }

    /**
     * Multiply / repeat the collection items.
     */
    public function multiply(int $times): static
    {
        if ($times < 1) {
            return new static([]);
        }

        $items = [];

        for ($i = 0; $i < $times; $i++) {
            foreach ($this->items as $value) {
                $items[] = $value;
            }
        }

        return new static($items);
    }

    /**
     * Partition into two collections using a callback / key comparison.
     *
     * @return static<int, static>
     */
    public function partition(callable|string|int $key, mixed $operator = null, mixed $value = null): static
    {
        $callback = func_num_args() === 1 && $this->useAsCallable($key)
            ? $key
            : $this->operatorForWhere(...func_get_args());

        [$passed, $failed] = ArrayAIO::partition($this->items, $callback);

        return new static([new static($passed), new static($failed)]);
    }

    /**
     * Get a random item or items.
     *
     * @return mixed|static
     */
    public function random(?int $number = null): mixed
    {
        if ($number === null) {
            return ArrayAIO::random($this->items);
        }

        return new static(ArrayAIO::random($this->items, $number));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable(mixed, mixed, int|string): mixed  $callback
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        return ArrayAIO::reduce($this->items, $callback, $initial);
    }

    /**
     * Reduce with spread arguments for array items.
     *
     * @param  callable(mixed, mixed...): mixed  $callback
     */
    public function reduceSpread(callable $callback, mixed ...$initial): mixed
    {
        $result = $initial;

        foreach ($this->items as $value) {
            $result = ArrayAIO::wrap($callback(...array_merge($result, ArrayAIO::wrap($value))));
        }

        return $result === [] ? null : (count($result) === 1 ? $result[0] : $result);
    }

    /**
     * Pass to a callback and return the result.
     *
     * @param  callable(static): mixed  $callback
     */
    public function pipe(callable $callback): mixed
    {
        return $callback($this);
    }

    /**
     * Pipe into a new class instance.
     *
     * @param  class-string  $class
     */
    public function pipeInto(string $class): object
    {
        return new $class($this);
    }

    /**
     * Pipe through a series of callbacks.
     *
     * @param  array<int, callable(mixed): mixed>  $pipes
     */
    public function pipeThrough(array $pipes): mixed
    {
        return ArrayAIO::reduce(
            $pipes,
            static fn (mixed $carry, callable $pipe): mixed => $pipe($carry),
            $this
        );
    }

    /**
     * Create a new collection for the given items (identity helper).
     */
    public function collect(): static
    {
        return new static($this->items);
    }

    /**
     * Tap into the collection then continue chaining.
     *
     * @param  callable(static): void  $callback
     */
    public function tap(callable $callback): static
    {
        $callback($this);

        return $this;
    }

    /**
     * Apply callback when condition is true.
     *
     * @param  bool|(callable(static): bool)  $condition
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function when(bool|callable $condition, callable $callback, ?callable $default = null): mixed
    {
        $value = \value($condition, $this);

        if ($value) {
            return $callback($this, $value) ?? $this;
        }

        if ($default) {
            return $default($this, $value) ?? $this;
        }

        return $this;
    }

    /**
     * Apply callback when condition is false.
     *
     * @param  bool|(callable(static): bool)  $condition
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function unless(bool|callable $condition, callable $callback, ?callable $default = null): mixed
    {
        return $this->when(! \value($condition, $this), $callback, $default);
    }

    /**
     * Apply callback when empty.
     *
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function whenEmpty(callable $callback, ?callable $default = null): mixed
    {
        return $this->when($this->isEmpty(), $callback, $default);
    }

    /**
     * Apply callback when not empty.
     *
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function whenNotEmpty(callable $callback, ?callable $default = null): mixed
    {
        return $this->when($this->isNotEmpty(), $callback, $default);
    }

    /**
     * Apply callback unless empty.
     *
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function unlessEmpty(callable $callback, ?callable $default = null): mixed
    {
        return $this->whenNotEmpty($callback, $default);
    }

    /**
     * Apply callback unless not empty.
     *
     * @param  callable(static): mixed  $callback
     * @param  (callable(static): mixed)|null  $default
     */
    public function unlessNotEmpty(callable $callback, ?callable $default = null): mixed
    {
        return $this->whenEmpty($callback, $default);
    }

    /**
     * Ensure values are of the given types (throws InvalidArgumentException otherwise).
     *
     * @param  string|array<int, string>  $type
     */
    public function ensure(string|array $type): static
    {
        $types = ArrayAIO::wrap($type);

        return $this->each(static function (mixed $item) use ($types): void {
            foreach ($types as $allowed) {
                if ($allowed === 'null' && $item === null) {
                    return;
                }

                if (is_object($item) && $item instanceof $allowed) {
                    return;
                }

                if (get_debug_type($item) === $allowed || gettype($item) === $allowed) {
                    return;
                }
            }

            throw new InvalidArgumentException(
                'Collection::ensure() detected an item of unexpected type [' . get_debug_type($item) . '].'
            );
        });
    }

    /**
     * Get the sole item matching the optional criteria, or fail.
     */
    public function sole(callable|string|int|null $key = null, mixed $operator = null, mixed $value = null): mixed
    {
        $filter = func_num_args() === 0
            ? null
            : (func_num_args() === 1 && $this->useAsCallable($key)
                ? $key
                : $this->operatorForWhere(...func_get_args()));

        $items = $filter ? $this->filter($filter) : $this;

        if ($items->isEmpty()) {
            throw new ItemNotFoundException('Item not found.');
        }

        if ($items->count() > 1) {
            throw new MultipleItemsFoundException($items->count() . ' items found.');
        }

        return $items->first();
    }

    /**
     * Get the item after the given value / truth test.
     */
    public function after(mixed $value, bool $strict = false): mixed
    {
        $found = false;

        foreach ($this->items as $item) {
            if ($found) {
                return $item;
            }

            $found = $this->useAsCallable($value)
                ? (bool) $value($item)
                : ($strict ? $item === $value : $item == $value);
        }

        return null;
    }

    /**
     * Get the item before the given value / truth test.
     */
    public function before(mixed $value, bool $strict = false): mixed
    {
        $previous = null;

        foreach ($this->items as $item) {
            $matches = $this->useAsCallable($value)
                ? (bool) $value($item)
                : ($strict ? $item === $value : $item == $value);

            if ($matches) {
                return $previous;
            }

            $previous = $item;
        }

        return null;
    }

    /**
     * Join values as a string.
     */
    public function implode(callable|string $value, ?string $glue = null): string
    {
        if ($this->useAsCallable($value)) {
            return ArrayAIO::implode($this->map($value)->all(), (string) $glue);
        }

        if ($glue !== null) {
            return ArrayAIO::implode($this->pluck($value)->all(), $glue);
        }

        return ArrayAIO::implode($this->items, $value);
    }

    /**
     * Join with a final separator.
     */
    public function join(string $glue, string $finalGlue = ''): string
    {
        return ArrayAIO::join($this->values()->all(), $glue, $finalGlue);
    }

    /**
     * Sum values, optionally by key/callback.
     *
     * @param  (callable(mixed): mixed)|string|null  $callback
     */
    public function sum(callable|string|null $callback = null): int|float
    {
        $callback = $callback === null
            ? static fn (mixed $value): mixed => $value
            : $this->valueRetriever($callback);

        return ArrayAIO::sum($this->map($callback)->all());
    }

    /**
     * Average values.
     *
     * @param  (callable(mixed): mixed)|string|null  $callback
     */
    public function avg(callable|string|null $callback = null): int|float|null
    {
        $callback = $callback === null
            ? static fn (mixed $value): mixed => $value
            : $this->valueRetriever($callback);

        $values = $this->map($callback)->filter(static fn (mixed $value): bool => ! is_null($value));

        if ($values->isEmpty()) {
            return null;
        }

        return $values->sum() / $values->count();
    }

    /**
     * Alias of avg().
     *
     * @param  (callable(mixed): mixed)|string|null  $callback
     */
    public function average(callable|string|null $callback = null): int|float|null
    {
        return $this->avg($callback);
    }

    /**
     * Median value.
     *
     * @param  (callable(mixed): mixed)|string|null  $key
     */
    public function median(callable|string|null $key = null): int|float|null
    {
        $values = ($key === null ? $this : $this->pluck($key))
            ->filter(static fn (mixed $value): bool => ! is_null($value))
            ->sort()
            ->values();

        $count = $values->count();

        if ($count === 0) {
            return null;
        }

        $middle = (int) ($count / 2);

        if ($count % 2) {
            return $values->get($middle);
        }

        return (new static([
            $values->get($middle - 1),
            $values->get($middle),
        ]))->average();
    }

    /**
     * Mode value(s).
     *
     * @param  (callable(mixed): mixed)|string|null  $key
     * @return static|null
     */
    public function mode(callable|string|null $key = null): ?static
    {
        if ($this->isEmpty()) {
            return null;
        }

        $counts = ($key === null ? $this : $this->pluck($key))->countBy()->sort()->reverse();
        $max = $counts->first();

        return $counts->filter(static fn (int $count): bool => $count === $max)->keys()->sort()->values();
    }

    /**
     * Min value.
     *
     * @param  (callable(mixed): mixed)|string|null  $callback
     */
    public function min(callable|string|null $callback = null): mixed
    {
        $callback = $callback === null
            ? static fn (mixed $value): mixed => $value
            : $this->valueRetriever($callback);

        return ArrayAIO::min($this->map($callback)->filter(static fn (mixed $value): bool => ! is_null($value))->all());
    }

    /**
     * Max value.
     *
     * @param  (callable(mixed): mixed)|string|null  $callback
     */
    public function max(callable|string|null $callback = null): mixed
    {
        $callback = $callback === null
            ? static fn (mixed $value): mixed => $value
            : $this->valueRetriever($callback);

        return ArrayAIO::max($this->map($callback)->filter(static fn (mixed $value): bool => ! is_null($value))->all());
    }

    /**
     * Percentage of items that pass the truth test.
     *
     * @param  callable(mixed, int|string): bool  $callback
     */
    public function percentage(callable $callback, int $precision = 2): float|int|null
    {
        if ($this->isEmpty()) {
            return null;
        }

        return round(
            $this->filter($callback)->count() / $this->count() * 100,
            $precision
        );
    }

    /**
     * Get the item after/before helpers and values as string for debugging.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @param  array<int, array{0: string|callable, 1?: string}>  $comparisons
     */
    protected function sortByMany(array $comparisons, int $options = SORT_REGULAR): static
    {
        $items = $this->items;

        uasort($items, function (mixed $a, mixed $b) use ($comparisons, $options): int {
            foreach ($comparisons as $comparison) {
                $prop = $comparison[0];
                $ascending = ($comparison[1] ?? 'asc') !== 'desc';
                $result = $this->useAsCallable($prop)
                    ? $prop($a) <=> $prop($b)
                    : \data_get($a, (string) $prop) <=> \data_get($b, (string) $prop);

                if ($result !== 0) {
                    return $ascending ? $result : -$result;
                }
            }

            return 0;
        });

        return new static($items);
    }

    /**
     * Build a where-style comparison callback.
     *
     * @return callable(mixed, int|string): bool
     */
    protected function operatorForWhere(callable|string|int $key, mixed $operator = null, mixed $value = null): callable
    {
        if ($this->useAsCallable($key)) {
            return $key;
        }

        if (func_num_args() === 1) {
            $value = true;
            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        return static function (mixed $item) use ($key, $operator, $value): bool {
            $retrieved = \data_get($item, (string) $key);

            return match ((string) $operator) {
                '=', '==' => $retrieved == $value,
                '!=', '<>' => $retrieved != $value,
                '<' => $retrieved < $value,
                '>' => $retrieved > $value,
                '<=' => $retrieved <= $value,
                '>=' => $retrieved >= $value,
                '===' => $retrieved === $value,
                '!==' => $retrieved !== $value,
                default => $retrieved == $value,
            };
        };
    }

    /**
     * @param  mixed  $value
     */
    protected function useAsCallable(mixed $value): bool
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * @param  (callable(mixed, int|string): mixed)|string|int|null  $value
     * @return callable(mixed, int|string): mixed
     */
    protected function valueRetriever(callable|string|int|null $value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return static fn (mixed $item): mixed => \data_get($item, $value === null ? null : (string) $value);
    }

    /**
     * @param  mixed  $items
     * @return array<int|string, mixed>
     */
    protected function getArrayableItems(mixed $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        if ($items instanceof self) {
            return $items->all();
        }

        if ($items instanceof JsonSerializable) {
            $serialized = $items->jsonSerialize();

            return is_array($serialized) ? $serialized : ArrayAIO::wrap($serialized);
        }

        if ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        if (is_object($items) && method_exists($items, 'toArray')) {
            return (array) $items->toArray();
        }

        return ArrayAIO::wrap($items);
    }
}
