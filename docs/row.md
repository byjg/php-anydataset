---
sidebar_position: 1
---

# The Row object

For each row returned by the dataset, you will receive a `Row` object. 

This object is a collection of key-value pairs, where the key is the field name and the value is the field value and
implements the `RowInterface` interface.

This is particularly useful to allow you to access the fields in a row in a consistent way, 
regardless of the dataset type.

## Interface

The `Row` object implements the following methods:

| Method                                  | Description                                                                               |
|-----------------------------------------|-------------------------------------------------------------------------------------------|
| `get($field)`                           | Get the value of the field.                                                               |
| `set($field, $value)`                   | Set the value of the field.                                                               |
| `set($field, $value, $append)`          | Set the value of the field. If append == true, it will add the value to the current field |
| `unset($field)`                         | Remove the field from the row.                                                            |
| `replace($field, $oldValue, $newValue)` | Replace the value of the field. If $oldValue is not set, nothing is changed.              |
| `toArray($fields)`                      | Convert the row to an array.                                                              |
| `entity()`                              | Return the entity object used to store the row contents.                                  |

## Example

```php
<?php
$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

while ($iterator->hasNext()) {
    $row = $iterator->moveNext();

    echo $row->get("field1");
}
```

## RowInterface implementations

The `RowInterface` has two implementations:

- `RowArray` - The default implementation
- `RowObject` - An implementation that uses an object to store the values

When we iterate over a dataset, we receive a `Row` object. 
The `Row` object decides how to store/get the values.

### RowArray

This is the default implementation. It uses an array to store the values.

### RowObject

This implementation uses an object to store the values. Some datasets, like the `AnyDatasetDb` dataset, can return a `RowObject` instead of a `RowArray`.
It doesn't change anything in the way you access the values, but it can be useful if you need to use the `entity()` method.

```php
<?php

class Model
{
    private int $id;
    private string $name;

    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }
    
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}

$model = new Model(id: 1, name: 'John');

$row = new RowObject($model);

$row->get('id'); // 1
$row->get('name'); // John
$row->entity(); // $model

$row->set('name', 'Mary');
$row->get('name'); // Mary
$row->entity()->getName(); // Mary

$row->entity()->setId('20');
$row->get('id'); // 20
```




