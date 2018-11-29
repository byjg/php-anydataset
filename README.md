# AnyDataset

[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg.com-brightgreen.svg)](http://opensource.byjg.com)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c/mini.png)](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/anydataset/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)
[![Build Status](https://travis-ci.org/byjg/anydataset.svg?branch=master)](https://travis-ci.org/byjg/anydataset)
[![Code Coverage](https://scrutinizer-ci.com/g/byjg/anydataset/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)


Anydataset Core Module. Anydataset is an agnostic data source abstraction layer in PHP. 

# Features

- Access different data sources using the same interface. 
- Iterable results
- Convert results to array

# Current Implementations

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


# Examples

## Iterating with foreach 

```php
<?php
$dataset = new \ByJG\AnyDataset\Core\AnyDataset("example");

$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    print $row->toArray();
}
```

## Filtering results

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation("field1", \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 10);
$iterator2 = $dataset->getIterator($filter);
```

## Conveting to Array

```php
<?php
$iterator = $dataset->getIterator();
print_r($iterator->toArray());
```

## Iterating with While

```php
<?php
$iterator = $dataset->getIterator();
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    
    print_r($row->get("field1"));
}
```


# Install

Just type: `composer require "byjg/anydataset=4.0.*"`

# Running Unit tests

```php
vendor/bin/phpunit
```

----
[Open source ByJG](http://opensource.byjg.com)
