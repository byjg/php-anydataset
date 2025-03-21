---
sidebar_position: 2
---

# Iterators

AnyDataset provides several iterator classes and interfaces to help you navigate through your data. These iterators follow the standard PHP `Iterator` interface, making them compatible with PHP's foreach loops and other iterator functions.

## Iterator Interfaces

### IteratorInterface

The base interface for all iterators in AnyDataset:

```php
interface IteratorInterface
{
    /**
     * Check if exists more records.
     */
    public function hasNext(): bool;
    
    /**
     * Get the next record. Return a Row object.
     */
    public function moveNext(): RowInterface|null;
    
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
abstract class GenericIterator implements IteratorInterface, Iterator
{
    public function hasNext(): bool;
    public function moveNext(): RowInterface|null;
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

### Using hasNext() and moveNext()

For more control over iteration:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    echo $row->get('name') . "\n";
}
``` 