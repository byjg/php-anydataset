---
sidebar_key: anydataset
sidebar_category: data-access
tags: [php, anydataset, databases]
---

# AnyDataset

Read any data source with the same code: a database, JSON, XML, a CSV or fixed-width file, a NoSQL store.
Every one of them gives you the same iterator of rows.

[![Sponsor](https://img.shields.io/badge/Sponsor-%23ea4aaa?logo=githubsponsors&logoColor=white&labelColor=0d1117)](https://github.com/sponsors/byjg)
[![Build Status](https://github.com/byjg/php-anydataset/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-anydataset/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](https://opensource.byjg.com)
[![Install MCP Server](https://img.shields.io/badge/Install-MCP_Server-8A2BE2?logo=modelcontextprotocol&logoColor=white)](https://opensource.byjg.com/docs/ai/mcpserver-byjg-docs/)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-anydataset/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-anydataset.svg)](https://opensource.byjg.com/license/)
[![GitHub release](https://img.shields.io/github/release/byjg/php-anydataset.svg)](https://github.com/byjg/php-anydataset/releases/)

It is the core component of the [Anydataset project](https://packagist.org/providers/byjg/anydataset-implementation), an agnostic data source abstraction layer for PHP.

## Why

Each kind of data has its own API: a PDO statement, `json_decode()` arrays, SimpleXML,
an `fgetcsv()` loop, a MongoDB cursor. Code written for one cannot read another, and each
needs its own loop, filter and conversion.

AnyDataset gives every source the same result: an iterator of `Row` objects.

```php
$iterator = $executor->getIterator('select * from users');           // database (anydataset-db)
$iterator = (new JsonDataset($json))->getIterator('/users');         // JSON (anydataset-json)
$iterator = TextFileDataset::getInstance('users.csv')
    ->withFieldParser(TextFileDataset::CSVFILE)
    ->getIterator();                                                 // CSV (anydataset-text)

foreach ($iterator as $row) {
    echo $row->get('name');
}
```

- **Change the source, keep the code.** The loop, `toArray()`, `toEntities()`, `first()`
  and `exists()` are the same whatever produced the iterator.
- **One filter, in memory or in SQL.** An `IteratorFilter` filters an in-memory
  `AnyDataset`, and the same filter becomes the `WHERE` clause of a database query.
- **Combine sources.** `import()` any iterator into an `AnyDataset` to sort, filter or save it.

## Key Features

- **Unified Interface**: Access different data sources (databases, arrays, XML, JSON, etc.) using the same interface
- **Flexible Iteration**: Multiple ways to iterate through your data
- **Powerful Filtering**: Filter your data using a SQL-like syntax
- **Data Transformation**: Convert between different formats (JSON, XML, arrays)
- **Validation**: Validate your data against rules
- **Extensible**: Create your own data source implementations

## Installation

```bash
composer require "byjg/anydataset"
```

## Quick Start

```php
<?php
# Create a dataset
$dataset = new \ByJG\AnyDataset\Core\AnyDataset("example");

# Get an iterator
$iterator = $dataset->getIterator();

# Iterate over the results
foreach ($iterator as $row) {
    print_r($row->toArray());
}
```

## Documentation

### Core Concepts
- [AnyDataset Overview](docs/anydataset-overview.md) - Core component overview and usage
- [The Row Object](docs/row.md) - Working with data rows
- [Iterators](docs/iterators.md) - Different ways to iterate through data

### Advanced Features
- [Filtering Results](docs/iterator-filter.md) - How to filter your data
- [Formatting Output](docs/row-output.md) - Transform your data into different formats
- [Field Validation](docs/row-validator.md) - Validate your data against rules
- [Data Population](docs/populate.md) - Populate objects with data

## Available Implementations

| Data Source Type      | Implementation         | Read/Write | Repository                                          |
|-----------------------|------------------------|:----------:|-----------------------------------------------------|
| **Core**              | AnyDataSet             |    R/W     | [Github](https://github.com/byjg/anydataset)        |
| **Databases**         | DbDriverInterface      |    R/W     | [Github](https://github.com/byjg/anydataset-db)     |
| **Arrays**            | ArrayDataSet           |     R      | [Github](https://github.com/byjg/anydataset-array)  |
| **Text Files**        | TextFileDataSet        |     R      | [Github](https://github.com/byjg/anydataset-text)   |
| **Fixed Width Files** | FixedTextFileDataSet   |     R      | [Github](https://github.com/byjg/anydataset-text)   |
| **XML**               | XmlDataSet             |     R      | [Github](https://github.com/byjg/anydataset-xml)    |
| **JSON**              | JSONDataSet            |     R      | [Github](https://github.com/byjg/anydataset-json)   |
| **SparQL**            | SparQLDataSet          |     R      | [Github](https://github.com/byjg/anydataset-sparql) |
| **NoSQL Document**    | NoSqlDocumentInterface |    R/W     | [Github](https://github.com/byjg/anydataset-nosql)  |
| **NoSQL Key/Value**   | KeyValueInterface      |    R/W     | [Github](https://github.com/byjg/anydataset-nosql)  |

## Example Code Snippets

### Filtering Data

```php
<?php
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->and("field1", \ByJG\AnyDataset\Core\Enum\Relation::Equal, 10);
$iterator = $dataset->getIterator($filter);
```

### Using Formatters

```php
<?php
use ByJG\AnyDataset\Core\Formatter\JsonFormatter;
use ByJG\AnyDataset\Core\Formatter\XmlFormatter;

$formatter = new XmlFormatter($anydataset->getIterator());
$formatter->raw();                           // Return a DOM object
$formatter->toText();                        // Return the XML as a text
$formatter->saveToFile("/path/to/file.xml"); // Save the XML Text to a file

// JsonFormatter has the same API; raw() returns the json_decode()d value
$json = (new JsonFormatter($anydataset->getIterator()))->toText();
```

Both formatters accept a `GenericIterator` or a single `RowInterface`.

## Running Tests

```bash
vendor/bin/phpunit
```

----
[Open source ByJG](https://opensource.byjg.com)