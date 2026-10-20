# Changelog - Version 6.0

## Overview

Version 6.0 is a major release that focuses on improving code architecture, enhancing type safety, and modernizing the codebase. This release includes significant refactoring of the Row object, removal of deprecated methods, and improved PHP version support.

## New Features

### Row Architecture Improvements
- **Introduced `RowInterface`**: New interface to define the contract for Row objects, enabling better abstraction and flexibility
- **Added `RowArray` and `RowObject` classes**: Specialized implementations for handling array-based and object-based rows
- **Row Factory Pattern**: The `Row` class now acts as a thin wrapper with a factory method (`Row::factory()`) to create appropriate implementations
- **Enhanced `set()` method**: Added optional `$append` parameter to support appending values to fields

### Filter Enhancements
- **New Filter Relations**: Added support for `IS_NULL` and `IS_NOT_NULL` relations in `IteratorFilter` for better null value filtering
- **Improved Filter Logic**: Refactored `evaluateFilterRecursive()` method by extracting helper methods:
  - `handleCloseGroup()` - Manages closing of filter groups
  - `handleOpenGroup()` - Manages opening of filter groups
  - `evaluateFilter()` - Evaluates individual filter conditions
  - `applyOperatorToResult()` - Applies logical operators to filter results

### Iterator Improvements
- **Added `toEntities()` method**: New method to convert iterator results directly into entity objects
- **Better Type Hints**: Improved type annotations throughout iterator classes for better IDE support and type safety

### Documentation
- **Comprehensive Documentation**: Added extensive documentation in the `docs/` directory:
  - `anydataset-overview.md` - Complete overview of AnyDataset usage
  - `iteratorfilter.md` - Detailed filtering guide
  - `iterators.md` - Iterator usage patterns
  - `populate.md` - Data population guide
  - `row.md` - Row object documentation
  - `rowoutput.md` - Output formatting guide
  - `rowvalidator.md` - Validation documentation

### Development Improvements
- **Composer Scripts**: Added convenience scripts for testing and static analysis:
  - `composer test` - Run PHPUnit tests
  - `composer psalm` - Run Psalm static analysis
- **Enhanced CI/CD**: Updated GitHub Actions workflow with improved Psalm setup and container configuration

## Bug Fixes

- Fixed `hasNext()` and `moveNext()` method implementations for better iterator reliability
- Improved error handling across components
- Fixed `saveToFile()` logic in formatters
- Fixed `getRelation()` logic in IteratorFilter
- Various Psalm static analysis fixes for improved type safety

## Breaking Changes

| Before (5.x) | After (6.0) | Description |
|--------------|-------------|-------------|
| `php: >=8.1 <8.4` | `php: >=8.3 <8.6` | **PHP Version Requirement**: Now requires PHP 8.3 or higher, added support for PHP 8.4 and 8.5 |
| `byjg/xmlutil: ^5.0` | `byjg/xmlutil: ^6.0` | **Dependency Update**: Requires xmlutil version 6.0 |
| `byjg/serializer: ^5.0` | `byjg/serializer: ^6.0` | **Dependency Update**: Requires serializer version 6.0 |
| `IteratorInterface::hasNext()` | *Removed* | **Removed Method**: The `hasNext()` method was removed from IteratorInterface. Use foreach iteration or check if `moveNext()` returns null instead |
| `IteratorInterface::moveNext()` | *Removed* | **Removed Method**: The `moveNext()` method was removed from IteratorInterface. Use foreach iteration instead |
| `IteratorInterface::count()` | *Removed* | **Removed Method**: The `count()` method was removed from IteratorInterface. Use `iterator_count()` or convert to array first |
| `Row::addField($name, $value)` | `Row::set($name, $value, true)` | **Method Removed**: Use `set()` with `$append = true` parameter instead |
| `Row::getAsArray($name)` | *Removed* | **Method Removed**: This method is no longer available in the new Row architecture |
| `Row::getFieldNames()` | *Removed* | **Method Removed**: Use `array_keys($row->toArray())` instead |
| `Row::removeField($name)` | `Row::unset($name)` | **Method Renamed**: Use the new `unset()` method |
| `Row::removeValue($name, $value)` | `Row::unset($name, $value)` | **Method Renamed**: Use the new `unset()` method with value parameter |
| `Row::replaceValue($field, $old, $new)` | `Row::replace($field, $old, $new)` | **Method Renamed**: Use the new `replace()` method |
| `Row::getAsRaw()` | *Removed* | **Method Removed**: Use `toArray()` instead |
| `Row::hasChanges()` | *Removed* | **Method Removed**: Change tracking functionality removed from Row |
| `Row::acceptChanges()` | *Removed* | **Method Removed**: Change tracking functionality removed from Row |
| `Row::rejectChanges()` | *Removed* | **Method Removed**: Change tracking functionality removed from Row |
| `Row::fieldExists($name)` | *Use entity()* | **Method Removed**: Access the underlying entity via `entity()` method |
| `Row::enableFieldNameCaseInSensitive()` | *Removed* | **Method Removed**: Case sensitivity handling removed |
| `Row::isFieldNameCaseSensitive()` | *Removed* | **Method Removed**: Case sensitivity handling removed |
| `IteratorFilter::addRelation()` | `IteratorFilter::and()` | **Deprecated**: While `addRelation()` still exists, prefer using `and()` for better readability |
| `IteratorFilter::addRelationOr()` | `IteratorFilter::or()` | **Deprecated**: While `addRelationOr()` still exists, prefer using `or()` for better readability |
| Method signatures with unused parameters | Cleaned signatures | **Parameter Removal**: Various methods had unused parameters removed across the codebase |

## Upgrade Path from 5.x to 6.x

### 1. Update PHP Version
Ensure you're running PHP 8.3 or higher:
```bash
php -v  # Should show PHP 8.3.0 or higher
```

### 2. Update Dependencies
Update your `composer.json`:
```bash
composer require "byjg/anydataset:^6.0"
composer update
```

### 3. Update Iterator Usage
Replace manual iteration with foreach loops:

**Before (5.x):**
```php
$iterator = $dataset->getIterator();
while ($iterator->hasNext()) {
    $row = $iterator->moveNext();
    // Process $row
}
```

**After (6.0):**
```php
$iterator = $dataset->getIterator();
foreach ($iterator as $row) {
    // Process $row
}
```

### 4. Update Row Method Calls

**Adding Fields:**
```php
// Before (5.x)
$row->addField('name', 'value');

// After (6.0)
$row->set('name', 'value', true);
```

**Removing Fields:**
```php
// Before (5.x)
$row->removeField('name');
$row->removeValue('name', 'specific-value');

// After (6.0)
$row->unset('name');
$row->unset('name', 'specific-value');
```

**Replacing Values:**
```php
// Before (5.x)
$row->replaceValue('field', 'old', 'new');

// After (6.0)
$row->replace('field', 'old', 'new');
```

**Getting Field Names:**
```php
// Before (5.x)
$fields = $row->getFieldNames();

// After (6.0)
$fields = array_keys($row->toArray());
```

**Getting Raw Data:**
```php
// Before (5.x)
$data = $row->getAsRaw();

// After (6.0)
$data = $row->toArray();
```

### 5. Update Filter Usage (Optional but Recommended)

For better readability, update filter method names:

```php
// Before (5.x)
$filter->addRelation('field', Relation::EQUAL, 'value');
$filter->addRelationOr('field2', Relation::GREATER_THAN, 10);

// After (6.0) - Recommended
$filter->and('field', Relation::EQUAL, 'value');
$filter->or('field2', Relation::GREATER_THAN, 10);
```

### 6. Remove Change Tracking Code

If you were using Row's change tracking features:

```php
// Before (5.x)
if ($row->hasChanges()) {
    $row->acceptChanges();
    // or
    $row->rejectChanges();
}

// After (6.0)
// Implement your own change tracking if needed, or handle changes at the application level
```

### 7. Update Case Sensitivity Handling

If you were using case-insensitive field names:

```php
// Before (5.x)
$row->enableFieldNameCaseInSensitive();

// After (6.0)
// Handle case sensitivity at the application level if needed
// Consider normalizing field names before storing/retrieving
```

### 8. Access Underlying Entity

If you need access to the raw array or object:

```php
// After (6.0)
$rawData = $row->entity();  // Returns the underlying array or object
```

### 9. Use New IS_NULL Relations

Take advantage of new null checking capabilities:

```php
// New in 6.0
$filter = new IteratorFilter();
$filter->and('field', Relation::IS_NULL);
$filter->or('field2', Relation::IS_NOT_NULL);
```

### 10. Testing

Run your test suite to identify any remaining compatibility issues:

```bash
composer test
composer psalm
```

## Notes

- **Performance**: The new Row architecture provides better performance through specialized implementations
- **Type Safety**: Enhanced type hints throughout the codebase improve IDE support and catch errors earlier
- **Documentation**: Comprehensive documentation is now available in the `docs/` directory
- **Backward Compatibility**: While this is a major version with breaking changes, the core API remains largely similar for most common use cases

For detailed examples and advanced usage, refer to the documentation in the `docs/` directory.
