---
sidebar_position: 1
sidebar_label: AnyDataset Overview
---

# AnyDataset Overview

The `AnyDataset` class is the core component of the AnyDataset library. It provides a simple way to store, manipulate, and retrieve data using a consistent interface.

## Introduction

AnyDataset is designed to be a flexible data container that can be populated from various sources and manipulated in a consistent way. It stores data as rows, where each row contains fields with values.

:::tip
AnyDataset automatically handles the `.anydata.xml` extension when loading files - you can specify just the filename without the extension!
:::

## Creating an AnyDataset

You can create an AnyDataset in several ways:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

// Empty dataset
$dataset = new AnyDataset();

// From an array of associative arrays
$data = [
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Mary']
];
$dataset = new AnyDataset($data);

// From an XML file (with .anydata.xml extension automatically added if not specified)
$dataset = new AnyDataset('data');
// Or with full extension
$dataset = new AnyDataset('data.anydata.xml');
```

## Basic Operations

### Adding Rows

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();

// Add a row
$dataset->appendRow(['id' => 1, 'name' => 'John']);

// Insert a row at a specific position
$dataset->insertRowBefore(0, ['id' => 2, 'name' => 'Mary']);

// Import rows from an iterator
$otherDataset = new AnyDataset($someData);
$dataset->import($otherDataset->getIterator());
```

### Removing Rows

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Row;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);

// Remove by index
$dataset->removeRow(0); // Removes John

// Remove by Row object
$row = new Row(['id' => 2, 'name' => 'Mary']);
$dataset->removeRow($row); // Removes Mary

// Remove current row (last one added or accessed)
$dataset->removeRow();
```

### Adding Fields to Current Row

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);

// Add a field to the current row
$dataset->addField('email', 'john@example.com');
```

### Sorting

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);

// Sort by id
$dataset->sort('id');
```

### Saving to a File

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);

// Save to the file specified in constructor
$dataset->save();

// Or save to a different file
$dataset->save('new_data.anydata.xml');
```

## Iterating Through Data

The most common way to access data in an AnyDataset is through iterators:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset($data);

// Get an iterator for all rows
$iterator = $dataset->getIterator();

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

## Getting Data as Arrays

You can extract data from the dataset as arrays:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\IteratorFilter;
use ByJG\AnyDataset\Core\Enum\Relation;

$dataset = new AnyDataset($data);

// Get all data as an array
$allData = $dataset->getIterator()->toArray();

// Get filtered data
$filter = new IteratorFilter();
$filter->and('age', Relation::GREATER_THAN, 30);
$filteredData = $dataset->getIterator($filter)->toArray();

// Get only specific fields
$namesOnly = $dataset->getIterator()->toArray(['name']);

// Get an array of values for a specific field
$allNames = $dataset->getArray('name');

// Get an array of filtered values for a specific field
$adultNames = $dataset->getArray('name', $filter);
```

## XML Representation

You can get the XML representation of the dataset:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);

$xml = $dataset->xml();
echo $xml;
```

This will output:

```xml
<?xml version="1.0" encoding="utf-8"?>
<anydataset>
    <row>
        <field name="id">1</field>
        <field name="name">John</field>
    </row>
    <row>
        <field name="id">2</field>
        <field name="name">Mary</field>
    </row>
</anydataset>
``` 