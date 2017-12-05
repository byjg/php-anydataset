# AnyDataset
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c/mini.png)](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/anydataset/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)
[![Build Status](https://travis-ci.org/byjg/anydataset.svg?branch=master)](https://travis-ci.org/byjg/anydataset)

## Description

A data abstraction layer in PHP to manipulate any set of data with a standardized interface from any data source.

## Features

### Read an write* with a number of data sources accessed by a standardized interface ([see more here](docs/Connecting-to-Data-Sources.md)):

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

### Querying Datasets (Text, Xml, Json, Sockets, Array, etc)

The easiest way to work is to get an repository and get an iterator for navigate throught the data.

```php
<?php
$repository = new \ByJG\AnyDataset\Dataset\TextFileDataset(
    'myfile',
    ['field1', 'field2', 'field3'],
    \ByJG\AnyDataset\Dataset\TextFileDataset::CSVFILE
);
$iterator = $repository->getIterator();

// and then:
foreach ($iterator as $row) {
    echo $row->get('field1');  // or $row->toArray();
}

// Or 
print_r($iterator->toArray());
```

### Querying Relational Databases

```php
<?php
$dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance('mysql://username:password@host/database');
$iterator = $dbDriver->getIterator('select * from table where field = :param', ['param' => 'value']);
```

### Cache results

You can easily cache your results with the DbCached class; You need to add to your project an
implementation of PSR-6. We suggested you add "byjg/cache".

```php
<?php
$dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance('mysql://username:password@host/database');
$dbCached = new \ByJG\AnyDataset\Store\DbCached($dbDriver, $psrCacheEngine, 30);

// Use the DbCached instance instead the DbDriver
$iterator = $dbCached->getIterator('select * from table where field = :param', ['param' => 'value']);
```

### Connection based on URI

The connection string for databases is based on URL. 

See below the current implemented drivers:

| Database      | Connection String                                     | Factory
| ------------- | ----------------------------------------------------- | -------------------------  |
| Sqlite        | sqlite:///path/to/file                                | getDbRelationalInstance()  |
| MySql/MariaDb | mysql://username:password@hostname:port/database      | getDbRelationalInstance()  |
| Postgres      | psql://username:password@hostname:port/database       | getDbRelationalInstance()  |
| Sql Server    | dblib://username:password@hostname:port/database      | getDbRelationalInstance()  |
| Oracle (OCI)  | oci://username:password@hostname:port/database        | getDbRelationalInstance()  |
| Oracle (OCI8) | oci8://username:password@hostname:port/database       | getDbRelationalInstance()  |
| Sql Relay     | sqlrelay://username:password@hostname:port/database   | getDbRelationalInstance()  |
| MongoDB       | mongodb://username:passwortd@host:port/database       | getNoSqlInstance()         |
| Amazon S3     | s3://key:secret@region/bucket                         | getKeyValueInstance()      |


### Querying Non-Relational Databases

```php
<?php
// Get a document
$dbDriver = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://host');
$document = $dbDriver->getDocumentById('iddcouemnt');

// Update some fields in there
$data = $document->getDocument();
$data['some_field'] = 'some_value';
$document->setDocument($data);

// Save the document
$dbDriver->save($document);
```

### Querying Key/Value Databases

```php
<?php
// Get a document
$dbDriver = \ByJG\AnyDataset\Factory::getKeyValueInstance('s3://awsid:secret@region');
$file = $dbDriver->get('key');

// Save the document
$dbDriver->put('key', file_get_contents('/path/to/file'));

// Delete the document
$dbDriver->remove('key');
```



### Load balance and connection pooling 

The API have support for connection load balancing, connection pooling and persistent connection with 
[SQL Relay](http://sqlrelay.sourceforge.net/) library (requires install)

You only need change your connection string to:

```
sqlrelay://root:somepass@server/schema
```

### And more

And more...


## Install

Just type: `composer require "byjg/anydataset=3.0.*"`

#### Running Unit tests

Running the Unit tests

```php
phpunit
```

#### Running database tests

Run integration tests require you to have the databases up e run with the follow configuration

- Server: XXXXX_container (where XXXX is the driver e.g. mysql)
- Database: test
- Username: root
- password: password

```
phpunit testsdb/PdoMySqlTest.php 
phpunit testsdb/PdoSqliteTest.php 
phpunit testsdb/PdoPostgresTest.php 
phpunit testsdb/PdoDblibTest.php 
phpunit testsdb/MongoDbDriverTest.php 
```

----
[Open source ByJG](http://opensource.byjg.com)
