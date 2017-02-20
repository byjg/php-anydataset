# Connecting To MongoDB

AnyDataset uses DataSet for connecting to data sources, like DBDataSet, XMLDataSet, SparQLDataSet and so on.
Now it is possible uses the same concept to connect to NoSQL databases. The first implementation is MongoDB.

## Creating the connection string

You can store your connections string in the file `config/anydatasetconfig.php` like 

```php
return [
    'connections' => [
        'conn_name' => [
            'url' => 'mongodb://root@localhost/dbname',
            'type' => 'dsn'
        ]
    ]
];
```

This string will be named 'conn_name' and will connect to localhost at default port (27017) in the dbname with no password.

The full connection string can be:

```
mongodb://username:password@server1,server2,server3/dbname?param1=value1&param2=value2
```

## Connecting to MongoDB

Once the connection string is defined you can connect to MongoDB using the following code:

```php
$db = new ByJG\AnyDataset\NoSQLDataSet('conn_name', 'collection_name');
```

## Inserting data to a Collection

To insert data:

```php
$document = array(
	'field1' => 'value1',
	'field2' => 'value2',
	'field3' => 'value3',
);
$db->insert($document);
```

Automatically is created the field 'created_at' with the MongoDate() of the current insert.

## Querying the collection

Querying the database will result a GenericIterator. It will be compatible with all objects.

### Retrieve all data

```php
$it = $db->getIterator();
foreach ($it as $sr)
{
	// Getting a single value
	Debug::PrintValue($sr->getField('field1'));

	// Getting a array of values
	Debug::PrintValue($sr->getFieldArray('field2'));

	// Getting the original info as json
	Debug::PrintValue($sr->getAsJson());

	// Getting the original as array
	Debug::PrintValue($sr->getRawFormat());
}
```

### Filtering the data

```php
// Search for a field1 with the value equals to 'value1'
$filter = array('field1' => 'value1');

// Retrieve only field2 and field3
$fields = array('field2', 'field3');

// Retrieve data
$it = $db->getIterator($filter, $fields);
foreach ($it as $sr)
{
	// Do something
}
```

### Update Data

AnyDataset uses the findAndModify mongodb method for update a field.

```php
// Create a document only with the fields needed to be changed
// The syntax is the same of Mongo.
// Note that the default update command is '$set', so you do not need create an array with the key '$set'
$doc = array(
	'field2' => 'New Value',
	'$inc' => array( 'counter' => 1 )
);

// Filter
$filter = array('_id' => new MongoID('5422dcc4dea5d5382b8b4568');

// Update
$result = $db->update($doc, $filter);
```

You can also add options to your update. The following options are available:

| Option       | Type    | Description |
| ------------ | ------- | ----------- |
| sort         | array   | Determines which document the operation will modify if the query selects multiple documents. findAndModify will modify the first document in the sort order specified by this argument. |
| remove       | boolean | Optional if update field exists. When TRUE, removes the selected document. The default is FALSE. |
| update       | array   | Optional if remove field exists. Performs an update of the selected document. |
| new          | boolean | Optional. When TRUE, returns the modified document rather than the original. The findAndModify method ignores the new option for remove operations. The default is FALSE. |
| upsert       | boolean | Optional. Used in conjunction with the update field. When TRUE, the findAndModify command creates a new document if the query returns no documents. The default is false. In MongoDB 2.2, the findAndModify command returns NULL when upsert is TRUE. |

```php
// Options
$options = array(
	"upsert" => true
);

$result = $db->update($doc, $filter, $options);
```
