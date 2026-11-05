---
sidebar_position: 8
sidebar_label: Writing your own iterator
---

# How to write your own dataset and iterator

Every iterator of the AnyDataset family (`AnyIterator`, `DbIterator`, `TextFileIterator`, `XmlIterator`,
`JsonIterator`...) extends `GenericIterator`. To read another source, write an iterator that extends it too:
it gets `foreach`, `toArray()`, `first()`, `exists()`... and works wherever the family's iterators do.

This guide reads a [JSON Lines](https://jsonlines.org) file (one JSON object per line).

## 1. The iterator: return the rows from `rows()`

A subclass implements only `rows()`, which returns a `Generator` of the rows. The generator must come from a
**static** function:

```php
<?php
use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;

class JsonLinesIterator extends GenericIterator
{
    /**
     * @param resource $handle
     */
    public function __construct(private $handle)
    {
    }

    protected function rows(): Generator
    {
        return self::read($this->handle);
    }

    /**
     * @param resource $handle
     */
    private static function read($handle): Generator
    {
        try {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) !== '') {
                    yield new Row(json_decode($line, true, flags: JSON_THROW_ON_ERROR));
                }
            }
        } finally {
            fclose($handle);   // at the last row, or when the iterator is dropped before it
        }
    }
}
```

- **Yield a `RowInterface`** for each row: `new Row($array)`, or `new Row($object)` for an entity. The keys of
  the generator are the keys of the iterator (`foreach ($iterator as $key => $row)`).
- **Pass what the generator needs as arguments** of the static function (the handle, the fields, the
  options), read from the properties in `rows()`.
- **Release what you opened in a `finally`** (a file, a cursor, a connection). It runs after the last row, and
  also when the iterator is dropped before the last row: `first()`, `exists()`, a `foreach` with `break`.

### Why a static function

A generator of a method keeps the object (`$this`) while it is not finished, and the iterator keeps the
generator. If `rows()` yielded itself, a partly read iterator would stay in memory, with its file open, until
PHP's garbage collector found the cycle. The generator of a static function does not reference the iterator:
it is destroyed with it, and its `finally` runs at once.

`GenericIterator` checks it: a `rows()` that yields itself throws `LogicException` ("must return the generator
of a static function, not yield itself") on the first read.

## 2. The dataset: open the source, return the iterator

The dataset is the entry point: it holds the configuration and returns a new iterator each time the rows are
read.

```php
<?php
use ByJG\AnyDataset\Core\GenericIterator;

class JsonLinesDataset
{
    public function __construct(private string $filename)
    {
    }

    public function getIterator(): GenericIterator
    {
        $handle = @fopen($this->filename, 'r');
        if ($handle === false) {
            throw new RuntimeException("Cannot open '$this->filename'");
        }
        return new JsonLinesIterator($handle);
    }
}
```

## 3. Use it

```php
<?php
// users.jsonl:
//   {"id":1,"name":"John"}
//   {"id":2,"name":"Mary"}
$dataset = new JsonLinesDataset('users.jsonl');

foreach ($dataset->getIterator() as $row) {
    echo $row->get('name');                   // John, Mary
}

$dataset->getIterator()->toArray();           // [['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Mary']]
$dataset->getIterator()->first();             // ['id' => 1, 'name' => 'John']: the file is closed at once
$dataset->getIterator()->exists();            // true
```

The iterator moves forward only, like every iterator of the family: to read the rows again, call
`getIterator()` again (here, the file is opened again). See [Iterators](iterators.md#reading-forward-only).

## Checklist

- [ ] The class extends `GenericIterator` and implements only `rows(): Generator`.
- [ ] `rows()` returns the generator of a **static** function, with what it needs as arguments.
- [ ] Each row is a `RowInterface` (`new Row(...)`).
- [ ] A resource opened for the rows is released in a `finally` of the generator.
- [ ] The dataset returns a new iterator from each `getIterator()` call.

A test that a partly read iterator releases its resource at once (here, counting the open streams):

```php
$streams = count(get_resources('stream'));
$iterator = $dataset->getIterator();
$iterator->first();
unset($iterator);
$this->assertCount($streams, get_resources('stream'));
```
