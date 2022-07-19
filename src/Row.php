<?php

namespace ByJG\AnyDataset\Core;

use ByJG\Serializer\SerializerObject;

class Row
{

    /**
     * @var array
     */
    private $row = [];

    /**
     * @var array
     */
    private $originalRow = [];

    /**
     * @var boolean
     */
    protected $fieldNameCaseSensitive = true;

    /**
     * Row constructor
     * 
     * @param Row|array|\stdClass|object $instance
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function __construct($instance = [])
    {
        if (is_array($instance)) {
            $this->row = $instance;
        } else {
            $this->row = SerializerObject::instance($instance)->serialize();
        }

        $this->acceptChanges();
    }

    /**
     * Add a string field to row
     * @param string $name
     * @param string|array|null $value
     * @return void
     */
    public function addField($name, $value)
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
     * @return null|string
     * @desc et the string value from a field name
     */
    public function get($name)
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
    public function getAsArray($fieldName)
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
    public function getFieldNames()
    {
        return array_keys($this->row);
    }

    /**
     * Set a string value to existing field name
     * @param string $name
     * @param string $value
     * @return void
     */
    public function set($name, $value)
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
     * @return null
     */
    public function removeField($fieldName)
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
    public function removeValue($fieldName, $value)
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
     * @param String $fieldName
     * @param String $oldvalue
     * @param String $newvalue
     * @return void
     */
    public function replaceValue($fieldName, $oldvalue, $newvalue)
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
    public function toArray($fields = [])
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
    public function getAsRaw()
    {
        return $this->originalRow;
    }

    /**
     *
     * @return bool
     */
    public function hasChanges()
    {
        return ($this->row != $this->originalRow);
    }

    /**
     * @return void
     */
    public function acceptChanges()
    {
        $this->originalRow = $this->row;
    }

    /**
     * @return void
     */
    public function rejectChanges()
    {
        $this->row = $this->originalRow;
    }

    /**
     * Override Specific implementation of setPropValue to Row
     *
     * @param Row $obj
     * @param string $propName
     * @param string $value
     * @return void
     */
    protected function setPropValue($obj, $propName, $value)
    {
        $obj->set($propName, $value);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function fieldExists($name)
    {
        return isset($this->row[$this->getHydratedFieldName($name)]);
    }

    /**
     * @return void
     */
    public function enableFieldNameCaseInSensitive() 
    {
        $this->row = array_change_key_case($this->row, CASE_LOWER);
        $this->originalRow = array_change_key_case($this->originalRow, CASE_LOWER);
        $this->fieldNameCaseSensitive = false;
    }

    /**
     * @return bool
     */
    public function isFieldNameCaseSensitive()
    {
        return $this->fieldNameCaseSensitive;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function getHydratedFieldName($name)
    {
        if (!$this->isFieldNameCaseSensitive()) {
            return strtolower($name);
        }

        return $name;
    }
}
