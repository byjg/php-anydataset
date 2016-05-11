<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\Serialize\BinderObject;
use ByJG\Serialize\DumpToArrayInterface;
use ByJG\Util\XmlUtil;
use DOMNode;
use UnexpectedValueException;

class SingleRow extends BinderObject implements DumpToArrayInterface
{

    /**
     * \DOMNode represents a SingleRow
     * @var DOMNode
     */
    private $_node = null;
    private $_row = null;
    private $_originalRow = null;

    /**
     * SingleRow constructor
     * @param array()
     */
    public function __construct($instance = null)
    {
        if (is_null($instance)) {
            $this->_row = array();
        } else if (is_array($instance)) {
            $this->_row = $instance;
        } else {
            $this->_row = array();
            $this->bind($instance);
        }

        $this->acceptChanges();
    }

    /**
     * Add a string field to row
     * @param string $name
     * @param string $value
     */
    public function addField($name, $value)
    {
        if (!array_key_exists($name, $this->_row)) {
            $this->_row[$name] = $value;
        } elseif (is_array($this->_row[$name])) {
            $this->_row[$name][] = $value;
        } else {
            $this->_row[$name] = array($this->_row[$name], $value);
        }
        $this->informChanges();
    }

    /**
     * @param string $name - Field name
     * @return string
     * @desc et the string value from a field name
     */
    public function getField($name)
    {
        if (!array_key_exists($name, $this->_row)) {
            return NULL;
        }

        $result = $this->_row[$name];
        if (is_array($result)) {
            return array_shift($result);
        } else {
            return $result;
        }
    }

    /**
     * Get array from a single field
     *
     * @param string $name
     * @return array
     */
    public function getFieldArray($name)
    {
        if (!array_key_exists($name, $this->_row)) {
            return array();
        }

        $result = $this->_row[$name];
        if (empty($result)) {
            return array();
        } elseif (is_array($result)) {
            return $result;
        } else {
            return array($result);
        }
    }

    /**
     * Return all Field Names from current SingleRow
     * @return array
     */
    public function getFieldNames()
    {
        return array_keys($this->_row);
    }

    /**
     * Set a string value to existing field name
     * @param string $name
     * @param string $value
     */
    public function setField($name, $value)
    {
        if (!array_key_exists($name, $this->_row)) {
            $this->addField($name, $value);
        } else {
            $this->_row[$name] = $value;
        }
        $this->informChanges();
    }

    /**
     * Remove specified field name from row.
     * @param string $name
     */
    public function removeFieldName($name)
    {
        if (array_key_exists($name, $this->_row)) {
            unset($this->_row[$name]);
            $this->informChanges();
        }
    }

    /**
     * Remove specified field name with specified value name from row.
     * @param string $name
     * @param $value
     */
    public function removeFieldNameValue($name, $value)
    {
        $result = $this->_row[$name];
        if (!is_array($result)) {
            if ($value == $result) {
                unset($this->_row[$name]);
                $this->informChanges();
            }
        } else {
            $qty = count($result);
            for ($i = 0; $i < $qty; $i++) {
                if ($result[$i] == $value) {
                    unset($result[$i]);
                    $this->informChanges();
                }
            }
            $this->_row[$name] = array_values($result);
        }
    }

    /**
     * Update a specific field and specific value with new value
     *
     * @param String $name
     * @param String $oldvalue
     * @param String $newvalue
     */
    public function setFieldValue($name, $oldvalue, $newvalue)
    {
        $result = $this->_row[$name];
        if (!is_array($result)) {
            if ($oldvalue == $result) {
                $this->_row[$name] = $newvalue;
                $this->informChanges();
            }
        } else {
            for ($i = sizeof($result) - 1; $i >= 0; $i--) {
                if ($result[$i] == $oldvalue) {
                    $this->_row[$name][$i] = $newvalue;
                    $this->informChanges();
                }
            }
        }
    }

    /**
     * Get the \DOMNode row objet
     * @return DOMNode
     */
    public function getDomObject()
    {
        if (is_null($this->_node)) {
            $this->_node = XmlUtil::createXmlDocumentFromStr("<row />");
            $root = $this->_node->getElementsByTagName("row")->item(0);
            foreach ($this->_row as $key => $value) {
                if (!is_array($value)) {
                    $field = XmlUtil::createChild($root, "field", $value);
                    XmlUtil::addAttribute($field, "name", $key);
                } else {
                    foreach ($value as $valueItem) {
                        $field = XmlUtil::createChild($root, "field", $valueItem);
                        XmlUtil::addAttribute($field, "name", $key);
                    }
                }
            }
        }
        return $this->_node;
    }

    public function toArray()
    {
        return $this->_row;
    }

    /**
     *
     * @return array
     */
    public function getAsJSON()
    {
        if (is_array($this->_row)) {
            return json_decode(json_encode($this->_row));
        } else {
            throw new UnexpectedValueException('I expected that getRawFormat is array() but ' . gettype($this->_row) . ' was given');
        }
    }

    /**
     * @return array
     */
    public function getOriginalRawFormat()
    {
        return $this->_originalRow;
    }

    /**
     *
     * @return bool
     */
    public function hasChanges()
    {
        return ($this->_row != $this->_originalRow);
    }

    /**
     *
     * @return bool
     */
    public function acceptChanges()
    {
        $this->_originalRow = $this->_row;
    }

    /**
     *
     * @return bool
     */
    public function rejectChanges()
    {
        $this->_row = $this->_originalRow;
    }

    protected function informChanges()
    {
        $this->_node = null;
    }

    /**
     * Override Specific implementation of setPropValue to SingleRow
     *
     * @param SingleRow $obj
     * @param string $propName
     * @param string $value
     */
    protected function setPropValue($obj, $propName, $value)
    {
        $obj->setField($propName, $value);
    }


}
