# AnyDataset
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c/mini.png)](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/anydataset/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)
[![Build Status](https://travis-ci.org/byjg/anydataset.svg?branch=master)](https://travis-ci.org/byjg/anydataset)
[![Code Coverage](https://scrutinizer-ci.com/g/byjg/anydataset/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)

## Description

Anydataset Core Module. Anydataset is an agnostic data source abstraction layer in PHP. 

## Features

- Access different data sources using the same interface. 
- Iterable results
- Convert results to array

## Current Implementations

* Array
* Relational Databases (based on PDO - Sqlite, MySql, Postgres, Sql Server, Oracle, and others)
* DBLib (SQL Server php native)
* OCI8 (Oracle php native interface)
* Text files (fixed and delimeted like CSV)
* Json documents
* Xml documents
* Sockets
* [MongoDB](docs/Connecting-to-MongoDB.md)
* Amazon Aws S3
* SparQL


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

## Install

Just type: `composer require "byjg/anydataset=4.0.*"`

#### Running Unit tests

Running the Unit tests

```php
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
