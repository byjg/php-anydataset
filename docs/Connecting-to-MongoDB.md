# Connecting To MongoDB

```php
<?php
$mongo = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://server');
```

The full connection string can be:

```
mongodb://username:password@server1,server2,server3/dbname?param1=value1&param2=value2
```

## Inserting data to a Collection

To insert data:

```php
<?php
$mongo = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://server');
$document = new \ByJG\AnyDataset\NoSqlDocument(
    null,
    'mycollection',
    [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => 'value3',
    ]
);
$mongo->save($document);
```

## Updating a document

Automatically is created the field 'created' and 'update' with the MongoDate() of the current insert.
Because there is no ID (first parameter) is an INSERT; 

```php
<?php
$mongo = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://server');
$document = new \ByJG\AnyDataset\NoSqlDocument(
    'someid',
    'mycollection',
    [
        'field1' => 'value1',
        'field2' => 'value2',
        'field3' => 'value3',
    ]
);
$mongo->save($document);
```

Automatically the field 'updated' is updated with the MongoDate() of the current update.
Because there is an ID (first parameter) is an UPDATE; 


## Querying the collection

Querying the database will result a GenericIterator. It will be compatible with all objects.

### Retrieve a document by Id

```php
<?php
$mongo = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://server');
$document = $mongo->getDocumentById($id);
if (!empty($document)) {
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```


### Retrieve all data

```php
<?php
$mongo = \ByJG\AnyDataset\Factory::getNoSqlInstance('mongodb://server');
$result = $mongo->getDocuments(null, 'mycollection');
foreach ($result as $document)
{
    print_r($document->getIdDocument());
    print_r($document->getDocument());
}
```

### Filtering the data

```php
<?php

$filter = new \ByJG\AnyDataset\Dataset\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Enum\Relation::EQUAL, 'value');

$result = $mongo->getDocuments($filter, 'mycollection');
foreach ($result as $document)
{
    // Do something
}
```
