---
sidebar_position: 2
sidebar_label: Row Object
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
| `toArray($fields = null)`               | Convert the row to an array. If $fields is provided, only those fields will be returned.  |
| `entity()`                              | Return the entity object used to store the row contents.                                  |

## Example

```php
<?php
$dataset = new AnyDataset($data);
$iterator = $dataset->getIterator();

while ($iterator->valid()) {
    $row = $iterator->current();

    echo $row->get("field1");
    $iterator->next();
}
```

## RowInterface implementations

The `RowInterface` has two implementations:

- `RowArray` - Uses an array to store the values
- `RowObject` - Uses an object to store the values

:::info
The `Row` class acts as a factory and wrapper for these implementations. When you create a `Row` object, it internally decides which implementation to use based on the data type provided.
:::

### RowArray

This is the default implementation when you provide an array. It uses an array to store the values.

Key features:
- Supports appending values to existing fields (turning them into arrays)
- Supports unsetting specific values from array fields
- Supports replacing specific values in array fields

```php
<?php
$row = new Row(['id' => 1, 'name' => 'John']);
// or
$row = new RowArray(['id' => 1, 'name' => 'John']);

$row->get('id'); // 1
$row->set('name', 'Mary'); // Changes name to Mary
$row->set('tags', 'php', true); // Creates tags field with 'php'
$row->set('tags', 'database', true); // Appends 'database' to tags, making it an array
```

### RowObject

This implementation is used when you provide an object. It uses the object's properties and getter/setter methods to access values.

Key features:
- Automatically uses getter/setter methods if they exist (e.g., `getName()`, `setName()`)
- Falls back to direct property access if methods don't exist
- Does not support appending, unsetting specific values, or replacing specific values

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

$row = new Row($model);
// or
$row = new RowObject($model);

$row->get('id'); // 1 (calls getId() method)
$row->get('name'); // John (calls getName() method)
$row->entity(); // Returns the original $model object

$row->set('name', 'Mary'); // Calls setName('Mary')
$row->get('name'); // Mary
$row->entity()->getName(); // Mary

$row->entity()->setId(20);
$row->get('id'); // 20
```

## Factory Method

The `Row` class provides a static factory method to create the appropriate implementation:

```php
<?php
// Creates a RowArray
$rowArray = Row::factory(['id' => 1, 'name' => 'John']);

// Creates a RowObject
$rowObject = Row::factory($model);
```




