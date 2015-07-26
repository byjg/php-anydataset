<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class Object
{

	/**
	 *
	 * @param mixed $source
	 */
	public function bind($source)
	{
		$this->bindObject($source, $this);
	}

	/**
	 *
	 * @param mixed $target
	 */
	public function bindTo($target)
	{
		$this->bindObject($this, $target);
	}

	/**
	 *
	 * @param mixed $source
	 * @param mixed $target
	 */
	public function bindObject($source, $target)
	{
		if ($source instanceof stdClass)
		{
			$this->bindStdClass($source, $target);
		}
		else if (is_array($source))
		{
			$this->bindArray($source, $target);
		}
		else if ($source instanceof SingleRow)
		{
			$this->bindArray($source->toArray(), $target);
		}
		else if ($source instanceof IteratorInterface)
		{
			if ($source->hasNext())
			{
				$this->bindArray($source->moveNext()->toArray(), $target);
			}
		}
		else
		{
			$this->bindGeneralObject($source, $target);
		}
	}


	/**
	 *
	 * @param mixed $source
	 * @param mixed $target
	 */
	protected function bindGeneralObject($source, $target)
	{
		$class = new ReflectionClass(get_class($source));
		$properties = $class->getProperties( ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PUBLIC );

		if (is_null($properties))
			return;

		foreach ($properties as $prop)
		{
			$propName = $prop->getName();

			// Ignore property from BaseModel
			if ($propName == "_propertyPattern")
				continue;

			// Remove Prefix "_" from Property Name to find a value
			if ($propName[0] == "_")
			{
				$propName = substr($propName, 1);
			}

			// Try to get the SOURCE Value
			$sourceValue = $this->getPropValue($source, $prop, $propName);

			// Set the Value
			if (!is_null($sourceValue))
			{
				$this->setPropValue($target, $propName, $sourceValue);
			}
		}
	}

	/**
	 *
	 * @param stdClass $source
	 * @param mixed $target
	 */
	protected function bindStdClass($source, $target)
	{
		$properties = get_object_vars($source);

		foreach ($properties as $propName => $sourceValue)
		{
			$this->setPropValue($target, $propName, $sourceValue);
		}
	}

	/**
	 *
	 * @param SingleRow $source
	 * @param mixed $target
	 */
	protected function bindArray($source, $target)
	{
		foreach ($source as $propName=>$value)
		{
			$this->setPropValue($target, $propName, $value);
		}
	}

	/**
	 *
	 * @param mixed $obj
	 * @param mixed $prop
	 * @param string $propName
	 * @return null
	 */
	protected function getPropValue($obj, $prop, $propName)
	{
		if (method_exists($obj, "getPropertyPattern"))
		{
			$propertyPattern = $obj->getPropertyPattern();
			if (!is_null($propertyPattern))
			{
				$propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
			}
		}

		if (method_exists($obj, 'get' . $propName))
		{
			if (is_callable([$obj, 'get' . $propName]))
			{
				return $obj->{'get' . $propName}();
			}
		}
		else if (is_null($prop))
		{
			return $obj->{$propName};
		}
		else if ($prop->isPublic())
		{
			return $prop->getValue($obj);
		}

		return null;
	}

	private $_propNameLower = null;

	/**
	 *
	 * @param mixed $obj
	 * @param string $propName
	 * @param string $value
	 */
	protected function setPropValue($obj, $propName, $value)
	{
		if (method_exists($obj, "getPropertyPattern"))
		{
			$propertyPattern = $obj->getPropertyPattern();
			if (!is_null($propertyPattern))
			{
				$propName = preg_replace($propertyPattern[0], $propertyPattern[1], $propName);
			}
		}

        if ($obj instanceof SingleRow)
		{
			$obj->setField($propName, $value);
		}
		else if (method_exists($obj, 'set' . $propName))
		{
			$obj->{'set' . $propName}($value);
		}
		else if (property_exists($obj, $propName))
		{
			$obj->{$propName} = $value;
		}
		else
		{
			if (is_null($this->_propNameLower))
			{
				$this->_propNameLower = array();

				$classVars = get_class_vars(get_class($obj));
				foreach ($classVars as $varKey=>$varValue)
				{
					$this->_propNameLower[strtolower($varKey)] = $varKey;
				}
			}

			if (isset($this->_propNameLower[strtolower($propName)]))
			{
				$obj->{$this->_propNameLower[strtolower($propName)]} = $value;
			}
		}
	}

}
