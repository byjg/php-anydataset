---
sidebar_position: 4
---


# Row Validator - Validate Field contents

The `RowValidator` class allows you to validate the contents of the fields in a `Row` object. 
You can use the `RowValidator` class to ensure that the data in the fields meets your requirements.

## Usage

```php
<?php
$validator = RowValidator::getInstance()
    ->requiredFields(["field1", "field2"])
    ->numericFields(['field1', 'field3'])
    ->regexValidation("field4", '/\d{4}-\d{2}-\d{2}/')
    ->customValidation("field3", function($value) {
        // Return any string containing the error message if validation FAILS
        // otherwise, just return null and the valition will pass.
    });

$validator->validate($row) // Will return an array with the error messages. Empty array if not errors.
```


