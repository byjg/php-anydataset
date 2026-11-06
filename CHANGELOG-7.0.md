# Changelog - Version 7.0

> **Status: in development.** This document tracks changes landing on the `7.0` branch.
> Nothing here is released yet, and the contents may still change.

## Breaking Changes

### Enum cases renamed to PascalCase

Enum cases follow the ByJG standard and are written in PascalCase. The backed values did not
change, so `->value`, `from()`, `tryFrom()` and stored data are not affected; only code that
names a case (or reads `->name`) has to be updated.

| Enum | Before (6.x) | After (7.0) |
|---|---|---|
| `Relation` | `EQUAL`, `LESS_THAN`, `GREATER_THAN`, `LESS_OR_EQUAL_THAN`, `GREATER_OR_EQUAL_THAN`, `NOT_EQUAL`, `STARTS_WITH`, `CONTAINS`, `IN`, `NOT_IN`, `IS_NULL`, `IS_NOT_NULL` | `Equal`, `LessThan`, `GreaterThan`, `LessThanOrEqualTo`, `GreaterThanOrEqualTo`, `NotEqual`, `StartsWith`, `Contains`, `In`, `NotIn`, `IsNull`, `IsNotNull` |

Not a plain change of casing: `Relation::LESS_OR_EQUAL_THAN` is now `Relation::LessThanOrEqualTo`; `Relation::GREATER_OR_EQUAL_THAN` is now `Relation::GreaterThanOrEqualTo`.

### Removed: `IteratorFilter::addRelation()` and `addRelationOr()`

Deprecated since 5.0. They were aliases: use `and()` and `or()`, which take the same arguments.

```php
// Before
$filter->addRelation('field', Relation::Equal, 'value');
$filter->addRelationOr('field2', Relation::GreaterThan, 10);
// After
$filter->and('field', Relation::Equal, 'value');
$filter->or('field2', Relation::GreaterThan, 10);
```

### `IteratorInterface` is an `IteratorAggregate`, not an `Iterator`

The iterators are built on generators: a `foreach` runs on the generator itself, with no PHP
method call for each row (see Performance). `IteratorInterface` extends `IteratorAggregate`
instead of `Iterator`, and still declares `valid()`, `current()`, `key()`, `next()` and
`rewind()` to step the rows by hand.

| | 6.x | 7.0 |
|---|---|---|
| `foreach`, `iterator_to_array()`, `yield from` | works | works |
| `valid()`, `current()`, `key()`, `next()`, `first()`, `exists()`, `toArray()`, `toEntities()` | works | works |
| `$iterator instanceof \Iterator` | `true` | `false` (`instanceof \Traversable` is `true`) |
| `new LimitIterator($iterator)`, `CachingIterator`, any wrapper that requires an `Iterator` | works | `TypeError`: wrap it with `new IteratorIterator($iterator)` |

Only code that type-checks or wraps the iterator as an `Iterator` has to change.

### A `GenericIterator` subclass implements `rows()`

`GenericIterator` is the base of every iterator. A subclass no longer implements `key()`,
`current()`, `next()` and `valid()`: it returns the generator of its rows from `rows()`, and that
generator must come from a **static** function.

```php
// Before (6.x)
class MyIterator extends GenericIterator
{
    private int $index = 0;
    public function __construct(private array $items) {}
    public function current(): mixed { return new Row($this->items[$this->index]); }
    public function key(): mixed { return $this->index; }
    public function next(): void { $this->index++; }
    public function valid(): bool { return $this->index < count($this->items); }
}

// After (7.0)
class MyIterator extends GenericIterator
{
    public function __construct(private array $items) {}

    protected function rows(): Generator
    {
        return self::walk($this->items);
    }

    private static function walk(array $items): Generator
    {
        foreach ($items as $item) {
            yield new Row($item);
        }
    }
}
```

A generator of a method keeps the iterator (`$this`) while it is not finished, and the iterator
keeps the generator: a partly read iterator (`first()`, a `foreach` with `break`) would stay in
memory, with its file or database cursor open, until the garbage collector found the cycle. From
a static function, the generator is destroyed with the iterator, and a `finally` in it releases
what it opened. A `rows()` that yields itself throws `LogicException`.

### The iterators move forward only

`rewind()` does nothing (as it already did for the database and file iterators): a `foreach`
continues from the current row, after `next()` or after another `foreach` stopped with `break`,
and an iterator already walked has no more rows. To read the rows again, get a new iterator from
the dataset (`getIterator()`).

```php
$iterator = $dataset->getIterator();
$first = $iterator->current();   // reads the first row, does not move
foreach ($iterator as $row) {}   // starts at that same first row
foreach ($iterator as $row) {}   // no rows: get a new iterator to read them again
```

## Performance

- Iterating is faster: a `foreach` runs on the generators, not on `valid()`/`current()`/`next()`
  of the iterator object. `AnyIterator` over 200,000 rows: ~46 ms in 6.x, ~27 ms in 7.0
  (PHP 8.5, Xdebug off).

## Bug Fixes

- `AnyDataset::removeRow()` with an index of 2 or more also dropped rows after it (removing
  index 2 of `a, b, c, d` left `a, b`), and never moved the current row, so a later
  `addField()` or `removeRow()` hit the wrong row or a missing index. It now removes only that
  row; the current row keeps pointing at the same row, or at the previous one when it is the
  one removed. An index out of range is ignored.

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

### ByJG dependencies

- `byjg/serializer` is now `^7.0`.
- `byjg/xmlutil` is now `^7.0`.

While 7.0 is unreleased these resolve to `7.0.x-dev` from each component's
`7.0` branch, via `minimum-stability: dev` with `prefer-stable: true`.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm is installed as `psalm/phar` instead of `vimeo/psalm`.

  `vimeo/psalm` lists the PHP versions it supports and no published release includes
  8.6, so as a dev dependency it made `composer install` fail on the 8.6 build job
  before any test ran. `psalm/phar` requires only `php ^8.2` and bundles its own
  dependencies, so it installs on every PHP version in the matrix and cannot conflict
  with the project's. Psalm itself still refuses to *run* on 8.6, which is why the
  Psalm job uses 8.5. `composer psalm` runs it.

- PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, breaking the
  8.3 floor.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job runs on PHP 8.5.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
- Documentation pages renamed to kebab-case: `docs/iteratorfilter.md` → `docs/iterator-filter.md`,
  `docs/rowoutput.md` → `docs/row-output.md` and `docs/rowvalidator.md` → `docs/row-validator.md`.
  Links to the old pages have to be updated: the published URLs change too (e.g. `/docs/php/anydataset/iteratorfilter`
  is now `/docs/php/anydataset/iterator-filter`), with no redirect.
