<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Repository\AnyDataset;
use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
use InvalidArgumentException;

class ArrayIIterator extends GenericIterator
{
	/**
	* @var array
	*/
	protected $_rows;

	/**
	 * Enter description here...
	 *
	 * @var array
	 */
	protected $_keys;

	/**
	/* @var int
	*/
	protected $_currentRow;

	/**
	* @return IteratorInterface
	*/
	public function __construct($rows)
	{
		if (!is_array($rows))
		{
			throw new InvalidArgumentException("ArrayIIterator must receive an array");
		}
		$this->_currentRow = 0;
		$this->_rows = $rows;
		$this->_keys = array_keys($rows);
	}

	/**
	* @return int
	*/
	public function count()
	{
		return count($this->_rows);
	}

	/**
	* @return bool
	*/
	public function hasNext()
	{
		return ($this->_currentRow < $this->count());
	}

	/**
	* @return SingleRow
	*/
	public function moveNext()
	{
		if ($this->hasNext())
		{
			$cols = array();
			$key = $this->_keys[$this->_currentRow];
			$cols = $this->_rows[$key];

			$any = new AnyDataset();
			$any->appendRow();
			$any->addField("__id", $this->_currentRow);
			$any->addField("__key", $key);
			foreach ($cols as $key=>$value)
			{
				$any->addField(strtolower($key), $value);
			}
			$it = $any->getIterator(null);
			$sr = $it->moveNext();
			$this->_currentRow++;
			return $sr;
		}
		else
		{
			return null;
		}
	}

 	function key()
 	{
 		return $this->_currentRow;
 	}
}
