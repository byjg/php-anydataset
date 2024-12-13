---
sidebar_position: 3
---

# RowOutput - Format a field

This class allows you to format a field value based on a pattern. 
The pattern can be a simple string or a custom function.

```php
<?php
$output = RowOutput::getInstance()
    ->addFormat("field1", "Test {field1}")
    ->addFormat("field2", "Showing {} and {field3}");
    ->addCustomFormat("field3", function ($row, $field, $value) {
        // return the formatted output.
        // $row: The row object with all values
        // $field: The field has been processed
        // $value: The field value
    });

// This will output the field1 formatted:
echo $output->print($row, "field1");

// This will apply the format defintion to all fields at once:
$ouput->apply($row);
```

Format pattern:

| Pattern        | Description                        |
|----------------|------------------------------------|
| `{}`           | The current value                  |
| `{.}`          | The field name                     |
| `{field_name}` | The value of $row->get(field_name) |


