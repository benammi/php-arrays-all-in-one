<?php

namespace Benammi\ArrayAIO;

/**
 * Proxies a method call onto each item in a collection (Laravel-style higher-order messages).
 *
 * Example: collect([...])->map->all()
 */
class HigherOrderCollectionProxy
{
    public function __construct(
        protected Collection $collection,
        protected string $method
    ) {
    }

    public function __get(string $key): mixed
    {
        return $this->collection->{$this->method}(static function (mixed $value) use ($key): mixed {
            return is_array($value) ? ($value[$key] ?? null) : ($value->{$key} ?? null);
        });
    }

    public function __call(string $method, array $parameters): mixed
    {
        return $this->collection->{$this->method}(static function (mixed $value) use ($method, $parameters): mixed {
            if (is_array($value)) {
                $value = new Collection($value);
            }

            return $value->{$method}(...$parameters);
        });
    }
}
