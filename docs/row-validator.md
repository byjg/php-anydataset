---
sidebar_position: 6
sidebar_label: Field Validation
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
        // otherwise, just return null and the validation will pass.
    });

$validator->validate($row); // Will return an array with the error messages. Empty array if no errors.
```

## Available Validation Methods

The `RowValidator` class provides the following validation methods:

| Method                                                                   | Description                                                                                             |
|--------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `requiredField(string $field): RowValidator`                             | Validates that the specified field is not empty.                                                        |
| `requiredFields(array $fieldList): RowValidator`                         | Validates that all fields in the array are not empty.                                                   |
| `numericFields(array $fieldList): RowValidator`                          | Validates that all fields in the array contain numeric values.                                          |
| `regexValidation(string\|array $field, string $regex): RowValidator`     |  Validates that the field value matches the specified regular expression.                               |
| `customValidation(string\|array $field, Closure $closure): RowValidator` | Applies a custom validation function to the field. The function should return an error message or null. |

## Validation Process

When you call the `validate()` method, the validator checks all the defined validations against the provided `Row` object:

1. For each field with validations defined, it runs all applicable validation checks
2. If a validation fails, an error message is added to the result array
3. The method returns an array of error messages (empty if all validations pass)

:::tip
Custom validation functions should return `null` for successful validation or a string error message for failures. This allows you to provide specific, user-friendly error messages.
:::

## Example with Multiple Validations

```php
<?php
$validator = RowValidator::getInstance();

// Add required field validations
$validator->requiredField("name");
$validator->requiredFields(["email", "phone"]);

// Add numeric validations
$validator->numericFields(["age", "zipcode"]);

// Add regex validation for email format
$validator->regexValidation("email", '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

// Add custom validation for age (must be between 18 and 120)
$validator->customValidation("age", function($value) {
    if ($value < 18) {
        return "Age must be at least 18";
    }
    if ($value > 120) {
        return "Age must be less than 120";
    }
    return null; // Validation passes
});

// Validate a row
$errors = $validator->validate($row);

// Check if there are any errors
if (empty($errors)) {
    echo "Validation passed!";
} else {
    echo "Validation failed with the following errors:";
    foreach ($errors as $error) {
        echo "- $error\n";
    }
}
```


