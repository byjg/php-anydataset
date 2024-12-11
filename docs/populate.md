---
sidebar_position: 5
---

# Populate AnyDataSet

You can populate an AnyDataSet with data from an array or a file.

## Populate from an Array

You can populate an AnyDataSet with data from an array.

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

You can populate an AnyDataSet with data from a file.

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset('data.anydataset.xml');
```

Note that the file must be in a format that AnyDataSet can understand.

It is a XML file with the following structure:

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

## From scratch

You can also create an empty dataset and populate it later.

```php
<?php
use ByJG\AnyDataset\Core\AnyDataset;

$dataset = new AnyDataset();
$dataset->appendRow(['id' => 1, 'name' => 'John']);
$dataset->appendRow(['id' => 2, 'name' => 'Mary']);
$dataset->appendRow(['id' => 3, 'name' => 'Paul']);
```


