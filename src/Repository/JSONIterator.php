<?php

namespace ByJG\AnyDataset\Repository;

use InvalidArgumentException;
use ByJG\AnyDataset\Exception\IteratorException;

class JSONIterator extends GenericIterator
{
	/**
	 * @var object
	 */
	private $_jsonObject;

	/**
	 * Enter description here...
	 *
	 * @var int
	 */
	private $_current = 0;

	public function __construct($jsonObject, $path = "", $throwErr = false)
	{
		if (!is_array($jsonObject))
		{
			throw new InvalidArgumentException("Invalid JSON object");
		}

		if ($path != "")
		{
			if ($path[0] == "/")
				$path = substr ($path, 1);

			$pathAr = explode("/", $path);

			$newjsonObject = $jsonObject;

			foreach($pathAr as $key)
			{
				if (array_key_exists($key, $newjsonObject))
				{
					$newjsonObject = $newjsonObject[$key];
				}
				elseif ($throwErr)
				{
					throw new IteratorException("Invalid path '$path' in JSON Object");
				}
				else
				{
					$newjsonObject = array();
					break;
				}
			}
			$this->_jsonObject = $newjsonObject;
		}
		else
			$this->_jsonObject = $jsonObject;

		$this->_current = 0;
	}

	public function Count()
	{
		return (count($this->_jsonObject));
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if ($this->_current < $this->Count())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
		}

		$sr = new SingleRow($this->_jsonObject[$this->_current++]);

		return 	$sr;
	}

 	function key()
 	{
 		return $this->_current;
 	}

}
?>
