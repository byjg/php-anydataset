---
sidebar_position: 2
---

# Filtering Results

You can filter the results of a query using the `IteratorFilter` class. This class simplifies creating filters 
for use with an `Iterator`. It is a standard feature across all AnyDataset implementations.

## Basic Usage

```php
<?php
// Create the IteratorFilter instance
$filter = new IteratorFilter();
$filter->and('field', Relation::EQUAL, 10);

// Create the Dataset
$dataset = new AnyDataset($file);

// get the iterator
$iterator = $dataset->getIterator($filter);

// This will return an iterator with only the rows where the field is equal to 10
```

## And / Or Conditions

For more complex queries, you can use the `and` and `or` methods to combine conditions.

For example:

If we want to filter the rows where the field is equal to 10 **or** 2, **and** the field2 is equal to 20:

```text
(field = 10 OR field = 2) AND field2 = 20
```

We can do this:

```php
<?php
$filter = new IteratorFilter();
$filter->startGroup('field', Relation::EQUAL, 10);
$filter->or('field', Relation::EQUAL, 2);
$filter->endGroup();
$filter->and('field2', Relation::EQUAL, 20);

$iterator = $dataset->getIterator($filter);
```


