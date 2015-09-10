<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class Object implements DumpToArrayInterface
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
        // Prepare the source object type
        if ($source instanceof IteratorInterface) {
            $sourceArray = $source->moveNext()->toArray();
        } else if ($source instanceof DumpToArrayInterface) {
            $sourceArray = $source->toArray();
        } else {
            $sourceArray = self::toArrayFrom($source);
        }

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
        if (is_array($source)) {
            return $source;
        }

        if ($source instanceof stdClass) {
            return self::toArrayFromStdClass($source);
        }

        return self::toArrayFromGeneralObject($source);
    }

    /**
     * Get all properties from a stdClass instance as an associative array
     *
     * @param mixed $source
     * @return array
     */
    protected static function toArrayFromStdClass($source)
    {
        $result = [];

        $properties = get_object_vars($source);

        foreach ($properties as $propName => $sourceValue) {
            $result[$propName] = $sourceValue;
        }

        return $result;
    }

    /**
     * Get all properties from an object instance as an associative array
     *
     * @param mixed $source
     * @return array
     */
    protected static function toArrayFromGeneralObject($source)
    {
        $result = [];

        $class = new ReflectionClass(get_class($source));
        $properties = $class->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC);

        if (is_null($properties)) {
            return $result;
        }

        foreach ($properties as $prop) {
            $propName = $prop->getName();

            // Ignore property from BaseModel
            if ($propName == "_propertyPattern") {
                continue;
            }

            // Remove Prefix "_" from Property Name to find a value
            if ($propName[0] == "_") {
                $propName = substr($propName, 1);
            }

            // Try to get the SOURCE Value
            $sourceValue = self::getPropValue($source, $prop, $propName);

            if (!is_null($sourceValue)) {
                $result[$propName] = $sourceValue;
            }
        }

        return $result;
    }

    /**
     * Get the property value
     *
     * @param mixed $obj
     * @param mixed $prop
     * @param string $propName
     * @return mixed
     */
    protected static function getPropValue($obj, $prop, $propName)
    {
        if (method_exists($obj, "getPropertyPattern")) {
            $propertyPattern = $obj->getPropertyPattern();
            if (!is_null($propertyPattern)) {
                $propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
            }
        }

        if (method_exists($obj, 'get' . $propName)) {
            if (is_callable([$obj, 'get' . $propName])) {
                return $obj->{'get' . $propName}();
            }
        } else if (is_null($prop)) {
            return $obj->{$propName};
        } else if ($prop->isPublic()) {
            return $prop->getValue($obj);
        }

        return null;
    }

    private static $propNameLower = [];

    /**
     * Set the property value
     *
     * @param mixed $obj
     * @param string $propName
     * @param string $value
     */
    protected static function setPropValue($obj, $propName, $value)
    {
        if (method_exists($obj, "getPropertyPattern")) {
            $propertyPattern = $obj->getPropertyPattern();
            if (!is_null($propertyPattern)) {
                $propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
            }
        }

        if ($obj instanceof SingleRow) {
            $obj->setField($propName, $value);
        } else if (method_exists($obj, 'set' . $propName)) {
            $obj->{'set' . $propName}($value);
        } elseif (isset($obj->{$propName})) {
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
