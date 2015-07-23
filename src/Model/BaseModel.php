<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Model\Object;

abstract class BaseModel extends Object
{

	protected $_propertyPattern = array('/([^A-Za-z0-9])/', '');

	/**
	 * Construct a model and optionally can set (bind) your properties base and the attribute matching from SingleRow, IIterator
	 * @param Object $object
	 * @return void
	 */
	public function __construct($object=null)
	{
		if (is_object($object) || is_array($object))
		{
			$this->bind($object);
		}
	}


	/**
	 * PropertyPattern can change how the protected property will be change to your related getter or setter.
	 *
	 * For example:
	 * protected $some_thing
	 *
	 * without propertypattern have to be:
	 * public function getSome_thing()
	 *
	 * but with the default property pattern ([^A-Za-z]) can be (excluing all char are not alpha)
	 * public function getSomething()
	 *
	 * @param $pattern
	 * @param $replace
	 * @return void
	 */
	public function setPropertyPattern($pattern, $replace)
	{
		if ($pattern == null) {
            $this->_propertyPattern = null;
        } else {
            $this->_propertyPattern = array(($pattern[0] != "/" ? "/" : "") . $pattern . ($pattern[strlen($pattern) - 1] != "/" ? "/" : ""), $replace);
        }
    }
	public function getPropertyPattern()
	{
		return $this->_propertyPattern;
	}

}
