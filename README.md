# AnyDataset

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![Build Status](https://github.com/byjg/anydataset/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/anydataset/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/anydataset/)
[![GitHub license](https://img.shields.io/github/license/byjg/anydataset.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/anydataset.svg)](https://github.com/byjg/anydataset/releases/)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/anydataset/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/byjg/anydataset/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)

Anydataset Core Module. Anydataset is an agnostic data source abstraction layer in PHP.

## Features

- Access different data sources using the same interface.
- Iterable results
- Convert results to array

## Current Implementations

{:.table}

| Object                 | Data Source           | Read | Write | Reference               |
| ---------------------- | --------------------- |:----:|:-----:| ----------------------- |
| DbDriverInterface      | Relational DB         | yes  | yes   | [Github](https://github.com/byjg/anydataset-db) |
| AnyDataSet             | Anydataset            | yes  | yes   | [Github](https://github.com/byjg/anydataset) |
| ArrayDataSet           | Array                 | yes  | no    | [Github](https://github.com/byjg/anydataset-array) |
| TextFileDataSet        | Delimited Fields      | yes  | no    | [Github](https://github.com/byjg/anydataset-text) |
| FixedTextFileDataSet   | Fixed Size fields     | yes  | no    | [Github](https://github.com/byjg/anydataset-text) |
| XmlDataSet             | Xml                   | yes  | no    | [Github](https://github.com/byjg/anydataset-xml) |
| JSONDataSet            | Json                  | yes  | no    | [Github](https://github.com/byjg/anydataset-json) |
| SparQLDataSet          | SparQl Repositories   | yes  | no    | [Github](https://github.com/byjg/anydataset-sparql) |
| NoSqlDocumentInterface | NoSql Document Based  | yes  | yes   | [Github](https://github.com/byjg/anydataset-nosql) |
| KeyValueInterface      | NoSql Key/Value Based | yes  | yes   | [Github](https://github.com/byjg/anydataset-nosql) |

## Examples

### Iterating with foreach

```php
<?php
$dataset = new \ByJG\AnyDataset\Core\AnyDataset("example");

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    print $row->toArray();
}
```

### Filtering results

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation("field1", \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 10);
$iterator2 = $dataset->getIterator($filter);
```

### Conveting to Array

```php
<?php
$iterator = $dataset->getIterator();
print_r($iterator->toArray());
```

### Iterating with While

```php
<?php
$iterator = $dataset->getIterator();
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    
    print_r($row->get("field1"));
}
```

### Validate Field contents

```php
<?php
$validator = RowValidator::getInstance()
    ->requiredFields(["field1", "field2"])
    ->numericFields(['field1', 'field3'])
    ->regexValidation("field4", '/\d{4}-\d{2}-\d{2}/')
    ->customValidation("field3", function($value) {
        // Return any string containing the error message if validation FAILS
        // otherwise, just return null and the valition will pass. 
    });

$validator->validate($row) // Will return an array with the error messages. Empty array if not errors. 
```

## Install

Just type: `composer require "byjg/anydataset=4.1.*"`

## Running Unit tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
