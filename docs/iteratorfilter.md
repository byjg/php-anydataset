---
sidebar_position: 4
sidebar_label: Filtering Results
---

# Filtering Results

You can filter the results of a query using the `IteratorFilter` class. This class simplifies creating filters 
for use with an `Iterator`. It is a standard feature across all AnyDataset implementations.

## Basic Usage

```php
<?php
// Create the IteratorFilter instance
$filter = new IteratorFilter();
$filter->and('field', Relation::EQUAL, 10);

// Create the Dataset
$dataset = new AnyDataset($file);

// get the iterator
$iterator = $dataset->getIterator($filter);

// This will return an iterator with only the rows where the field is equal to 10
```

:::info
The `IteratorFilter` class is a standard feature across all AnyDataset implementations, making it easy to filter data from any source (databases, XML, JSON, arrays, etc.) using the same interface.
:::

## Available Relations

The `IteratorFilter` class supports the following relations from the `Relation` enum:

| Relation                          | Description                                 |
|-----------------------------------|---------------------------------------------|
| `Relation::EQUAL`                 | Field equals the value                      |
| `Relation::NOT_EQUAL`             | Field does not equal the value              |
| `Relation::GREATER_THAN`          | Field is greater than the value             |
| `Relation::LESS_THAN`             | Field is less than the value                |
| `Relation::GREATER_OR_EQUAL_THAN` | Field is greater than or equal to the value |
| `Relation::LESS_OR_EQUAL_THAN`    | Field is less than or equal to the value    |
| `Relation::CONTAINS`              | Field contains the value (substring match)  |
| `Relation::STARTS_WITH`           | Field starts with the value                 |
| `Relation::IN`                    | Field value is in the provided array        |
| `Relation::NOT_IN`                | Field value is not in the provided array    |
| `Relation::IS_NULL`               | Field value is null                         |
| `Relation::IS_NOT_NULL`           | Field value is not null                     |

## Filter Methods

The `IteratorFilter` class provides the following methods:

| Method                                                                                                 | Description                                                      |
|--------------------------------------------------------------------------------------------------------|------------------------------------------------------------------|
| `and(string $name, Relation $relation, mixed $value = null)`                                           | Adds an AND condition to the filter                              |
| `or(string $name, Relation $relation, mixed $value)`                                                   | Adds an OR condition to the filter                               |
| `startGroup(string $name, Relation $relation, mixed $value)`                                           | Starts a group of conditions with the first condition            |
| `endGroup()`                                                                                           | Ends a group of conditions                                       |
| `match(array $array)`                                                                                  | Applies the filter to an array of rows and returns matching rows |
| `format(IteratorFilterFormatter $formatter, ?string $tableName, array &$params, string $returnFields)` | Formats the filter for use with a specific formatter (e.g., SQL) |
| `addRelation(string $name, Relation $relation, mixed $value)`                                          | Alias for `and()` (deprecated)                                   |
| `addRelationOr(string $name, Relation $relation, mixed $value)`                                        | Alias for `or()` (deprecated)                                    |

## Complex Conditions with AND / OR

For more complex queries, you can use the `and`, `or`, `startGroup`, and `endGroup` methods to combine conditions.

### Example: Simple AND/OR Conditions

```php
<?php
$filter = new IteratorFilter();
$filter->and('field1', Relation::EQUAL, 10);
$filter->and('field2', Relation::GREATER_THAN, 20);
$filter->or('field3', Relation::CONTAINS, 'test');

// This will match rows where:
// (field1 = 10 AND field2 > 20) OR field3 contains 'test'
```

### Example: Grouped Conditions

If we want to filter the rows where the field is equal to 10 **or** 2, **and** the field2 is equal to 20:

```text
(field = 10 OR field = 2) AND field2 = 20
```

We can do this:

```php
<?php
$filter = new IteratorFilter();
$filter->startGroup('field', Relation::EQUAL, 10);
$filter->or('field', Relation::EQUAL, 2);
$filter->endGroup();
$filter->and('field2', Relation::EQUAL, 20);

$iterator = $dataset->getIterator($filter);
```

### Example: NULL Checking

To filter for rows where a field is null or not null:

```php
<?php
$filter = new IteratorFilter();
// Find rows where email is null
$filter->and('email', Relation::IS_NULL);

// Or find rows where email is not null
$filter->and('email', Relation::IS_NOT_NULL);
```

## Using with Different Formatters

The `IteratorFilter` can be used with different formatters to generate XPath or other query formats:

```php
<?php
// Create a filter
$filter = new IteratorFilter();
$filter->and('name', Relation::CONTAINS, 'John');
$filter->and('age', Relation::GREATER_THAN, 30);

// Format for XPath (useful when working with XML datasets)
$params = [];
$xpath = $filter->format(new IteratorFilterXPathFormatter(), null, $params);
// Result: "/anydataset/row[contains(field[@name='name'], 'John') and field[@name='age'] > '30']"

// The base IteratorFilterFormatter can be used for custom formatting
$formatted = $filter->format(new IteratorFilterFormatter(), null, $params);
// Result: "str_contains(%name, 'John') and %age > 30"
```

## Static Factory Method

You can also use the static factory method to create a new instance:

```php
<?php
$filter = IteratorFilter::getInstance()
    ->and('field1', Relation::EQUAL, 10)
    ->and('field2', Relation::GREATER_THAN, 20);
```


