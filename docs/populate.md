---
sidebar_position: 7
sidebar_label: Data Population
---

# Populate AnyDataSet

You can populate an AnyDataSet with data from various sources including arrays, files, or by building it incrementally.

## Populate from an Array

You can populate an AnyDataSet with data from an array. Each element in the array should be an associative array representing a row.

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$data = [
    ['id' => 1, 'name' => 'John'],
    ['id' => 2, 'name' => 'Mary'],
    ['id' => 3, 'name' => 'Paul'],
];

$dataset = new AnyDataset($data);
```

## Populate from a File

You can populate an AnyDataSet with data from an XML file. The file extension should be `.anydata.xml` or you can specify the full filename with extension.

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

// Will automatically append .anydata.xml if no extension is provided
$dataset = new AnyDataset('data');

// Or with explicit extension
$dataset = new AnyDataset('data.anydata.xml');
```

### AnyDataset XML Format

The AnyDataset XML file must follow this structure:

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
    <row>
        <field name="id">3</field>
        <field name="name">Paul</field>
    </row>
</anydataset>
```

## Building from Scratch

You can also create an empty dataset and populate it incrementally.

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();

// Add rows one by one
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);
```

## Importing from Another Iterator

You can import data from any iterator that implements the `GenericIterator` interface:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

// Assuming $otherIterator is a valid GenericIterator
$dataset = new AnyDataset();
$dataset->import($otherIterator);
```

## Inserting Rows at Specific Positions

You can insert a row at a specific position in the dataset:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);

// Insert a row at position 1 (between John and Paul)
$dataset->insertRowBefore(1, ['id' => 2, 'name' => 'Mary']);
```

## Removing Rows

You can remove rows from the dataset:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;
use ByJG\AnyDataset\Core\Row;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);

// Remove by index
$dataset->removeRow(1); // Removes Mary

// Remove by Row object
$row = new Row(['id' => 3, 'name' => 'Paul']);
$dataset->removeRow($row); // Removes Paul
```

## Saving to a File

After populating your dataset, you can save it to an XML file:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);

// Save to the original file (if loaded from a file)
$dataset->save();

// Or save to a new file
$dataset->save('new_data.anydata.xml');
```

## Sorting the Dataset

You can sort the dataset by a specific field:

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);

// Sort by id
$dataset->sort('id');

// Now the dataset is ordered by id: John, Mary, Paul
```


