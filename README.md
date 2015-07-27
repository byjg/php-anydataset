# AnyDataset
[![Build Status](https://travis-ci.org/byjg/anydataset.svg?branch=master)](https://travis-ci.org/byjg/anydataset)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c/mini.png)](https://insight.sensiolabs.com/projects/159bc0fe-42dd-4022-a3a2-67e871491d6c)

## Description

A data abstraction layer in PHP to manipulate any set of data with a standardized interface from any data source.

## Features

### Read an write* with a standardized interface the follow data sources:
* Array
* Relational Databases (based on PDO)
* DBLib (SQL Server php native - windows only)
* OCI8 (Oracle php native interface)
* Text files (fixed and delimeted like CSV)
* Json documents
* Xml documents
* Sockets
* MongoDB
* SparQL

## Examples

The easiest way to work is to get an repository and get an iterator for navigate throught the data.

```php
$repository = new TextFileDataset('myfile', ['field1', 'field2', 'field3'], TextFileDataset::CSVFILE);
$iterator = $repository->getIterator();

// and then:
foreach ($iterator as $row) {
    echo $row->getField('field1');  // or $row->toArray();
}

// Or 
print_r($iterator->toArray());
```

### Cache results

You can easily cache your results with the CachedDBDataset class;

```php
$repository = new CachedDBDataset('connection', new \ByJG\Cache\MemcachedEngine());
$iterator = $repository->getIterator('select field1, field2 from sometable', 120); // cache for 2 minutes
```

### Relational database connections string based on URL

The connection string for relational databases is based on URL. Connect to mysql in the server localhost with user 'root'
and password 'somepass' is easy as `mysql://root:somepass@localhost/schema`

You can store your connections string in the file `config/anydatasetconfig.php` like 

```php
return [
    'connections' => [
        'development' => [
            'name' => 'mysql://root:somepass@localhost/schema',
            'type' => 'dsn'
        ],
        'staging' => [
            'name' => 'mysql://root:otherpass@192.168.1.205:3307/schema',
            'type' => 'dsn'
        ]
    ]
];
```

### Load balance and connection pooling 

The API have support for connection load balancing, connection pooling and persistent connection with 
[SQL Relay](http://sqlrelay.sourceforge.net/) library (requires install)

You only need change your connection string to:

```
sqlrelay://root:somepass@server/schema
```

### Object binding

```php
class MyModel extends \ByJG\AnyDataset\Model\BaseModel
{
    protected $prop1;
    public getProp1(){ ... };
    public setProp1($value) { ... };
}

// And then you can bind values from another object or array
$myModel = new MyModel($_REQUEST); // suppose that $_REQUEST['prop1'] exists
```

### Create DAL class easily
   
```php
class MyDAL extends BaseDBAccess
{
    public function getById($id)
    {
        return $this->getIterator('select * from sometable where id = :id', [ 'id' => $id ]);
    }

    // Some query need to be cached for 180 seconds
    public function getExpensiveQuery()
    {
        return $this->getIterator('select * from expensive_query', null, 180);
    }
}
```

### And more

And more...


## Install

Just type: `composer require "byjg/anydataset=~1.0"`

## Running Tests

```php
cd tests
phpunit --bootstrap bootstrap.php .
```