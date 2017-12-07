# AnyDataset
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c/mini.png)](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/byjg/anydataset/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)
[![Build Status](https://travis-ci.org/byjg/anydataset.svg?branch=master)](https://travis-ci.org/byjg/anydataset)
[![Code Coverage](https://scrutinizer-ci.com/g/byjg/anydataset/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/byjg/anydataset/?branch=master)

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

The API have support for connection load balancing, connection pooling and persistent connection.

There is the Route class an DbDriverInterface implementation with route capabilities. Basically you have to define 
the routes and the system will choose the proper DbDriver based on your route definition.

Example:

```php
<?php
$dbDriver = new \ByJG\AnyDataset\Store\Route();

// Define the available connections (even different databases)
$dbDriver
    ->addDbDriverInterface('route1', 'sqlite:///tmp/a.db')
    ->addDbDriverInterface('route2', 'sqlite:///tmp/b.db')
    ->addDbDriverInterface('route3', 'sqlite:///tmp/c.db')
;

// Define the route
$dbDriver
    ->addRouteForWrite('route1')
    ->addRouteForRead('route2', 'mytable')
    ->addRouteForRead('route3')
;

// Query the database
$iterator = $dbDriver->getIterator('select * from mytable'); // Will select route2
$iterator = $dbDriver->getIterator('select * from othertable'); // Will select route3
$dbDriver->execute('insert into table (a) values (1)'); // Will select route1;
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

The easiest way to run the tests is:

**Prepare the environment**

```php
npm i
node_modules/.bin/usdocker --refresh
node_modules/.bin/usdocker -v --no-link mssql up
node_modules/.bin/usdocker -v --no-link mysql up
node_modules/.bin/usdocker -v --no-link postgres up
node_modules/.bin/usdocker -v --no-link mongodb up
```

**Run the tests**


```
phpunit testsdb/PdoMySqlTest.php 
phpunit testsdb/PdoSqliteTest.php 
phpunit testsdb/PdoPostgresTest.php 
phpunit testsdb/PdoDblibTest.php 
phpunit testsdb/MongoDbDriverTest.php 
```

Optionally you can set the password for Mysql and PostgresSQL

```bash
export MYSQL_PASSWORD=newpassword    # use '.' if want have a null password
export PSQL_PASSWORD=newpassword     # use '.' if want have a null password
```

----
[Open source ByJG](http://opensource.byjg.com)
