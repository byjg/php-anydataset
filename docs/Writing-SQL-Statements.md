# Writing SQL Statements

AnyDataset database class will require you write raw SQL Statements.

```php
<?php
$db = \ByJG\AnyDataset\Factory::getDbRelationalInstance('mysql://localhost');
$iterator = $db->getIterator('select * from table where field = :value', ['value' => 10]);
foreach ($iterator as $row) {
    // Do Something
    // $row->getField('field');
}
```

## Using IteratorFilter in order to get the SQL

You can use the IteratorFilter object to make easier create SQL

```php
<?php
// Create the IteratorFilter instance
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, 10);

// Generate the SQL
$param = [];
$formatter = new \ByJG\AnyDataset\Core\IteratorFilterSqlFormatter();
$sql = $formatter->format(
    $filter->getRawFilters(),
    'mytable',
    $param,
    'field1, field2'
);

// Execute the Query
$iterator = $db->getIterator($sql, $param);
```

## Using IteratorFilter with Literal values

Sometimes you need an argument as a Literal value like a function or an explicit conversion. 

In this case you have to create a class that expose the "__toString()" method

```php
<?php

// The class with the "__toString()" exposed
class MyLiteral
{
    //...
    
    public function __toString() {
        return "cast('10' as integer)";
    }
}

// Create the literal instance
$literal = new MyLiteral();

// Using the IteratorFilter:
$filter = new \ByJG\AnyDataset\Core\IteratorFilter();
$filter->addRelation('field', \ByJG\AnyDataset\Core\Enum\Relation::EQUAL, $literal);
```

