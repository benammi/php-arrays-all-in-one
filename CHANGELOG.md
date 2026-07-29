# Changelog

All notable changes to `php-arrays-all-in-one` will be documented in this file.

## 1.1.0 - 2026-07-29

### Added
- Laravel-style fluent `Collection` class with chaining, ArrayAccess, Countable, IteratorAggregate, and JsonSerializable
- Global `collect()`, `value()`, and `data_get()` helpers
- Higher-order collection messages (e.g. `$collection->map->name`)
- Collection methods covering filter/where, map/reduce, sort/group, slice/chunk, merge/zip, aggregates, conditionals, and more
- `ItemNotFoundException` and `MultipleItemsFoundException` for `sole()` / `firstOrFail()`
- Pest coverage for the Collection API

## 1.0.0 - 2026-07-29

### Added
- Complete `ArrayAIO` toolkit covering conversion, access, immutable updates, filtering, mapping, sorting, combining, aggregation, and utilities
- Dotted-key helpers (`getDot`, `setDot`, `hasDot`, `forgetDot`, `dot`, `undot`)
- Full PHPDoc on every public method
- Expanded Pest test suite covering the public API
- Package README with installation guide and API reference

### Changed
- Minimum PHP version is now `^8.1`
- `stringToArray()` with an empty separator now uses `str_split` safely
- Development dependency: Pest `^2.0|^3.0`

## 0.0.1 - 2022-09-23

- Initial release with `stringToArray()` and `mergeArrays()`
