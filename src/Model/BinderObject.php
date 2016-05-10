<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\SingleRow;
use stdClass;

class BinderObject implements DumpToArrayInterface
{

    /**
     * Bind the properties from an object to the properties matching to the current instance
     *
     * @param mixed $source
     */
    public function bind($source)
    {
        self::bindObject($source, $this);
    }

    /**
     * Bind the properties from the current instance to the properties matching to an object
     *
     * @param mixed $target
     */
    public function bindTo($target)
    {
        self::bindObject($this, $target);
    }

    /**
     * Get all properties from the current instance as an associative array
     *
     * @return array The object properties as array
     */
    public function toArray()
    {
        return self::toArrayFrom($this);
    }

    /**
     * Bind the properties from a source object to the properties matching to a target object
     *
     * @param mixed $source
     * @param mixed $target
     */
    public static function bindObject($source, $target)
    {
        $sourceArray = self::toArrayFrom($source);

        foreach ($sourceArray as $propName => $value) {
            self::setPropValue($target, $propName, $value);
        }
    }

    /**
     * Get all properties from a source object as an associative array
     *
     * @param mixed $source
     * @return array
     */
    public static function toArrayFrom($source)
    {
        // Prepare the source object type
        $object = new SerializerObject($source);
        $object->setStopFirstLevel(true);
        return $object->build();
    }

    protected static $propNameLower = [];

    /**
     * Set the property value
     *
     * @param mixed $obj
     * @param string $propName
     * @param string $value
     */
    protected static function setPropValue($obj, $propName, $value)
    {
        if ($obj instanceof SingleRow) {
            $obj->setField($propName, $value);
        } else if (method_exists($obj, 'set' . $propName)) {
            $obj->{'set' . $propName}($value);
        } elseif (isset($obj->{$propName}) || $obj instanceof stdClass) {
            $obj->{$propName} = $value;
        } else {
            // Check if source property have property case name different from target
            $className = get_class($obj);
            if (!isset(self::$propNameLower[$className])) {
                self::$propNameLower[$className] = [];

                $classVars = get_class_vars($className);
                foreach ($classVars as $varKey => $varValue) {
                    self::$propNameLower[$className][strtolower($varKey)] = $varKey;
                }
            }

            $propLower = strtolower($propName);
            if (isset(self::$propNameLower[$className][$propLower])) {
                $obj->{self::$propNameLower[$className][$propLower]} = $value;
            }
        }
    }
}
