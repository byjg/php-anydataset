---
sidebar_position: 5
sidebar_label: Formatting Output
---

# RowOutput - Format Field Values

The `RowOutput` class allows you to format field values based on patterns or custom functions.
This is useful for transforming raw data into display-friendly formats without modifying the original data.

## Basic Usage

```php
<?php
$output = RowOutput::getInstance()
    ->addFormat("field1", "Test {field1}")
    ->addFormat("field2", "Showing {} and {field3}")
    ->addCustomFormat("field3", function ($row, $field, $value) {
        // return the formatted output.
        // $row: The row object with all values
        // $field: The field being processed
        // $value: The field value
        return strtoupper($value);
    });

// This will output the field1 formatted:
echo $output->print($row, "field1");

// This will apply the format definition to all fields at once:
$formattedRow = $output->apply($row);
```

## Format Patterns

When using the `addFormat` method, you can use the following placeholders in your pattern:

| Pattern        | Description                        |
|----------------|------------------------------------|
| `{}`           | The current field's value          |
| `{.}`          | The current field's name           |
| `{field_name}` | The value of $row->get(field_name) |

## Available Methods

The `RowOutput` class provides the following methods:

| Method                                                     | Description                                                                       |
|------------------------------------------------------------|-----------------------------------------------------------------------------------|
| `getInstance()`                                            | Static factory method to create a new RowOutput instance.                         |
| `print(RowInterface $row, string $field): mixed`           | Returns the formatted value for a specific field.                                 |
| `apply(RowInterface $row): RowInterface`                   | Returns a new Row object with all fields formatted according to defined patterns. |
| `addFormat(string $field, string $pattern): static`        | Adds a pattern-based format for a specific field.                                 |
| `addCustomFormat(string $field, Closure $closure): static` | Adds a custom formatting function for a specific field.                           |

## Examples

### Pattern-based Formatting

```php
<?php
$row = new Row([
    'id' => 123,
    'name' => 'John Doe',
    'price' => 29.99
]);

$output = RowOutput::getInstance()
    ->addFormat('id', 'ID: {}')
    ->addFormat('name', 'Name: {name}')
    ->addFormat('price', '${} ({id})');

echo $output->print($row, 'id');    // "ID: 123"
echo $output->print($row, 'name');  // "Name: John Doe"
echo $output->print($row, 'price'); // "$29.99 (123)"
```

### Custom Formatting

```php
<?php
$row = new Row([
    'date' => '2023-12-15',
    'price' => 29.99,
    'status' => 'active'
]);

$output = RowOutput::getInstance()
    ->addCustomFormat('date', function ($row, $field, $value) {
        // Convert YYYY-MM-DD to MM/DD/YYYY
        $parts = explode('-', $value);
        return $parts[1] . '/' . $parts[2] . '/' . $parts[0];
    })
    ->addCustomFormat('price', function ($row, $field, $value) {
        // Format as currency
        return '$' . number_format($value, 2);
    })
    ->addCustomFormat('status', function ($row, $field, $value) {
        // Convert status to badge style
        return '<span class="badge">' . ucfirst($value) . '</span>';
    });

$formattedRow = $output->apply($row);
// $formattedRow now contains formatted values for all fields
```
