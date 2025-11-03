---
sidebar_position: 3
sidebar_label: Iterators
---

# Iterators

AnyDataset provides several iterator classes and interfaces to help you navigate through your data. These iterators follow the standard PHP `Iterator` interface, making them compatible with PHP's foreach loops and other iterator functions.

## Iterator Interfaces

### IteratorInterface

The base interface for all iterators in AnyDataset:

```php
interface IteratorInterface extends Iterator
{
    /**
     * Get an array representation of the iterator.
     */
    public function toArray(array $fields = []): array;
}
```

## Iterator Classes

### GenericIterator

An abstract base class that implements both `IteratorInterface` and PHP's `Iterator` interface:

```php
abstract class GenericIterator implements IteratorInterface
{
    public function toArray(array $fields = []): array;
    
    // Abstract methods that must be implemented by subclasses
    abstract public function key(): mixed;
    abstract public function current(): mixed;
    abstract public function next(): void;
    abstract public function valid(): bool;
    
    // Implemented method
    public function rewind(): void;
}
```

### AnyIterator

A concrete implementation of `GenericIterator` that works with arrays of `Row` objects:

```php
class AnyIterator extends GenericIterator
{
    public function __construct(array $list);
    public function key(): mixed;
    public function current(): mixed;
    public function next(): void;
    public function valid(): bool;
}
```

## Using Iterators

### Basic Iteration

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

// Using while loop
while ($iterator->valid()) {
    $row = $iterator->current();
    echo $row->get('name') . "\n";
    $iterator->next();
}

// Or using foreach (preferred)
foreach ($iterator as $row) {
    echo $row->get('name') . "\n";
}
```

### Filtered Iteration

You can use `IteratorFilter` to filter the results:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\Enum\Relation;

$dataset = new AnyDataset($data);

$filter = new IteratorFilter();
$filter->and('age', Relation::GREATER_THAN, 30);

$iterator = $dataset->getIterator($filter);

foreach ($iterator as $row) {
    // This will only iterate over rows where age > 30
    echo $row->get('name') . " is " . $row->get('age') . " years old\n";
}
```

### Converting to Array

You can convert an iterator to an array:

:::tip
Use `toArray()` with specific field names to extract only the data you need, reducing memory usage for large datasets.
:::

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

// Get all data as an array of associative arrays
$allData = $iterator->toArray();

// Get only specific fields
$namesAndAges = $iterator->toArray(['name', 'age']);
```

### Getting the underlying entities (objects)

If your rows were created from objects (models), you can retrieve those original entities:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();
$entities = $iterator->toEntities();
```
