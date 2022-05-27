<?php

namespace ByJG\AnyDataset\Core;

use ByJG\Serializer\BinderObject;
use ByJG\Serializer\DumpToArrayInterface;
use ByJG\Util\XmlUtil;
use UnexpectedValueException;

class Row extends BinderObject implements DumpToArrayInterface
{

    /**
     * \DOMNode represents a Row
     * @var \DOMElement
     */
    private $node = null;
    private $row = null;
    private $originalRow = null;

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
            $this->row = array();
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
        if (!array_key_exists($name, $this->row)) {
            $this->row[$name] = $value;
        } elseif (is_array($this->row[$name])) {
            $this->row[$name][] = $value;
        } else {
            $this->row[$name] = array($this->row[$name], $value);
        }
        $this->informChanges();
    }

    /**
     * @param string $name - Field name
     * @return string
     * @desc et the string value from a field name
     */
    public function get($name)
    {
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
     */
    public function set($name, $value)
    {
        if (!array_key_exists($name, $this->row)) {
            $this->addField($name, $value);
        } else {
            $this->row[$name] = $value;
        }
        $this->informChanges();
    }

    /**
     * Remove specified field name from row.
     *
     * @param string $fieldName
     */
    public function removeField($fieldName)
    {
        if (array_key_exists($fieldName, $this->row)) {
            unset($this->row[$fieldName]);
            $this->informChanges();
        }
    }

    /**
     * Remove specified field name with specified value name from row.
     *
     * @param string $fieldName
     * @param $value
     */
    public function removeValue($fieldName, $value)
    {
        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($value == $result) {
                unset($this->row[$fieldName]);
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
            $this->row[$fieldName] = array_values($result);
        }
    }

    /**
     * Update a specific field and specific value with new value
     *
     * @param String $fieldName
     * @param String $oldvalue
     * @param String $newvalue
     */
    public function replaceValue($fieldName, $oldvalue, $newvalue)
    {
        $result = $this->row[$fieldName];
        if (!is_array($result)) {
            if ($oldvalue == $result) {
                $this->row[$fieldName] = $newvalue;
                $this->informChanges();
            }
        } else {
            for ($i = count($result) - 1; $i >= 0; $i--) {
                if ($result[$i] == $oldvalue) {
                    $this->row[$fieldName][$i] = $newvalue;
                    $this->informChanges();
                }
            }
        }
    }

    /**
     * Get the \DOMElement row objet
     *
     * @return \DOMElement
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function getAsDom()
    {
        if (is_null($this->node)) {
            $this->node = XmlUtil::createXmlDocumentFromStr("<row></row>");
            $root = $this->node->getElementsByTagName("row")->item(0);
            foreach ($this->row as $key => $value) {
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
        return $this->node;
    }

    public function toArray()
    {
        return $this->row;
    }

    /**
     *
     * @return array
     */
    public function getAsJSON()
    {
        if (is_array($this->row)) {
            return json_decode(json_encode($this->row));
        } else {
            throw new UnexpectedValueException(
                'I expected that getRawFormat is array() but ' . gettype($this->row) . ' was given'
            );
        }
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
     *
     */
    public function acceptChanges()
    {
        $this->originalRow = $this->row;
    }

    /**
     *
     */
    public function rejectChanges()
    {
        $this->row = $this->originalRow;
    }

    protected function informChanges()
    {
        $this->node = null;
    }

    /**
     * Override Specific implementation of setPropValue to Row
     *
     * @param Row $obj
     * @param string $propName
     * @param string $value
     */
    protected function setPropValue($obj, $propName, $value)
    {
        $obj->set($propName, $value);
    }
}
