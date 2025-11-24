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

    /**
     * Get an array of the underlying entities.
     */
    public function toEntities(): array;

    /**
     * Get the first element of the iterator, or null if empty.
     */
    public function first(): mixed;

    /**
     * Get the first element of the iterator, or throw an exception if empty.
     */
    public function firstOrFail(): mixed;

    /**
     * Check if the iterator has any elements.
     */
    public function exists(): bool;

    /**
     * Check if the iterator has any elements, or throw an exception if empty.
     */
    public function existsOrFail(): bool;
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

### Getting the First Element

You can retrieve the first element from an iterator without manually iterating:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

// Get the first element, or null if empty
$first = $iterator->first();

if ($first !== null) {
    echo "First item found\n";
}
```

#### With Objects

When your dataset contains objects, `first()` returns the underlying entity:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

class User {
    public $id;
    public $name;

    public function __construct($id, $name) {
        $this->id = $id;
        $this->name = $name;
    }
}

$dataset = new AnyDataset();
$dataset->appendRow(new User(1, "John"));
$dataset->appendRow(new User(2, "Jane"));

$firstUser = $dataset->getIterator()->first();
// $firstUser is a User object
echo $firstUser->name; // "John"
```

#### With Arrays

When your dataset contains arrays, `first()` returns the array:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Jane']);

$firstRow = $dataset->getIterator()->first();
// $firstRow is an array
echo $firstRow['name']; // "John"
```

### Getting the First Element or Fail

Use `firstOrFail()` when you want to ensure there's at least one element:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\NotFoundException;

$dataset = new AnyDataset();

try {
    $first = $dataset->getIterator()->firstOrFail();
} catch (NotFoundException $e) {
    echo "No results found: " . $e->getMessage();
}
```

### Checking if Iterator Has Elements

You can check if an iterator contains any elements:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

if ($iterator->exists()) {
    echo "Iterator has elements\n";
} else {
    echo "Iterator is empty\n";
}
```

### Checking if Iterator Has Elements or Fail

Use `existsOrFail()` when you want to ensure the iterator is not empty:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Exception\NotFoundException;

$dataset = new AnyDataset($data);

try {
    $dataset->getIterator()->existsOrFail();
    echo "Iterator has elements\n";
} catch (NotFoundException $e) {
    echo "Iterator is empty: " . $e->getMessage();
}
```

### Combining with Filters

These methods work seamlessly with filters:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\Enum\Relation;

$dataset = new AnyDataset();
$dataset->appendRow(['name' => 'John', 'age' => 25]);
$dataset->appendRow(['name' => 'Jane', 'age' => 35]);
$dataset->appendRow(['name' => 'Bob', 'age' => 45]);

$filter = IteratorFilter::getInstance()
    ->and('age', Relation::GREATER_THAN, 30);

// Get first person over 30
$first = $dataset->getIterator($filter)->first();
echo $first['name']; // "Jane"

// Check if anyone is over 30
if ($dataset->getIterator($filter)->exists()) {
    echo "Found people over 30\n";
}
```
