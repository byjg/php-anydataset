<?php

namespace ByJG\AnyDataset\Core;

use ByJG\Serializer\Serialize;

class Row
{

    /**
     * @var array
     */
    private array $row = [];

    /**
     * @var array
     */
    private array $originalRow = [];

    /**
     * @var boolean
     */
    protected bool $fieldNameCaseSensitive = true;

    /**
     * Row constructor
     *
     * @param object|array $instance
     */
    public function __construct(object|array $instance = [])
    {
        if (is_array($instance)) {
            $this->row = $instance;
        } else {
            $this->row = Serialize::from($instance)->toArray();
        }

        $this->acceptChanges();
    }

    /**
     * Add a string field to row
     * @param string $name
     * @param array|string|null $value
     * @return void
     */
    public function addField(string $name, array|string|null $value): void
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            $this->row[$name] = $value;
        } elseif (is_array($this->row[$name])) {
            $this->row[$name][] = $value;
        } else {
            $this->row[$name] = array($this->row[$name], $value);
        }
    }

    /**
     * @param string $name - Field name
     * @return mixed
     * @desc et the string value from a field name
     */
    public function get(string $name): mixed
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            return null;
        }

        $result = $this->row[$name];
        if (is_array($result)) {
            return array_shift($result);
        } else {
            return $result;
        }
    }

    /**
     * Get array from a single field
     *
     * @param string $fieldName
     * @return array
     */
    public function getAsArray(string $fieldName): array
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        if (!array_key_exists($fieldName, $this->row)) {
            return [];
        }

        $result = $this->row[$fieldName];

        if (empty($result)) {
            return [];
        }

        return (array)$result;
    }

    /**
     * Return all Field Names from current Row
     * @return array
     */
    public function getFieldNames(): array
    {
        return array_keys($this->row);
    }

    /**
     * Set a string value to existing field name
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set(string $name, mixed $value): void
    {
        $name = $this->getHydratedFieldName($name);

        if (!array_key_exists($name, $this->row)) {
            $this->addField($name, $value);
        } else {
            $this->row[$name] = $value;
        }
    }

    /**
     * Remove specified field name from row.
     *
     * @param string $fieldName
     */
    public function removeField(string $fieldName): void
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        if (array_key_exists($fieldName, $this->row)) {
            unset($this->row[$fieldName]);
        }
    }

    /**
     * Remove specified field name with specified value name from row.
     *
     * @param string $fieldName
     * @param mixed $value
     * @return void
     */
    public function removeValue(string $fieldName, mixed $value): void
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($value == $result) {
                unset($this->row[$fieldName]);
            }
        } else {
            $qty = count($result);
            for ($i = 0; $i < $qty; $i++) {
                if ($result[$i] == $value) {
                    unset($result[$i]);
                }
            }
            $this->row[$fieldName] = array_values($result);
        }
    }

    /**
     * Update a specific field and specific value with new value
     *
     * @param string $fieldName
     * @param mixed $oldvalue
     * @param mixed $newvalue
     */
    public function replaceValue(string $fieldName, mixed $oldvalue, mixed $newvalue): void
    {
        $fieldName = $this->getHydratedFieldName($fieldName);

        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($oldvalue == $result) {
                $this->row[$fieldName] = $newvalue;
            }
        } else {
            for ($i = count($result) - 1; $i >= 0; $i--) {
                if ($result[$i] == $oldvalue) {
                    $this->row[$fieldName][$i] = $newvalue;
                }
            }
        }
    }

    /**
     * @param array|null $fields
     * @return array
     */
    public function toArray(?array $fields = []): array
    {
        if (empty($fields)) {
            return $this->row;
        }
        
        $fieldAssoc = array_combine($fields, array_fill(0, count($fields), null));
        return array_intersect_key(array_merge($fieldAssoc, $this->row), $fieldAssoc);
    }

    /**
     * @return array
     */
    public function getAsRaw(): array
    {
        return $this->originalRow;
    }

    /**
     *
     * @return bool
     */
    public function hasChanges(): bool
    {
        return ($this->row != $this->originalRow);
    }

    /**
     * @return void
     */
    public function acceptChanges(): void
    {
        $this->originalRow = $this->row;
    }

    /**
     * @return void
     */
    public function rejectChanges(): void
    {
        $this->row = $this->originalRow;
    }

    /**
     * Override Specific implementation of setPropValue to Row
     *
     * @param Row $obj
     * @param string $propName
     * @param mixed $value
     * @return void
     */
    protected function setPropValue(Row $obj, string $propName, mixed $value): void
    {
        $obj->set($propName, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function fieldExists(string $name): bool
    {
        return isset($this->row[$this->getHydratedFieldName($name)]);
    }

    /**
     * @return void
     */
    public function enableFieldNameCaseInSensitive(): void
    {
        $this->row = array_change_key_case($this->row, CASE_LOWER);
        $this->originalRow = array_change_key_case($this->originalRow, CASE_LOWER);
        $this->fieldNameCaseSensitive = false;
    }

    /**
     * @return bool
     */
    public function isFieldNameCaseSensitive(): bool
    {
        return $this->fieldNameCaseSensitive;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getHydratedFieldName(string $name): string
    {
        if (!$this->isFieldNameCaseSensitive()) {
            return strtolower($name);
        }

        return $name;
    }
}
