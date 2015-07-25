<?php

namespace ByJG\AnyDataset\Model;

use ByJG\AnyDataset\Exception\IteratorException;
use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\IteratorInterface;
use ByJG\AnyDataset\Repository\SingleRow;
use InvalidArgumentException;

abstract class BaseModelCollection extends GenericIterator
{

	protected $_items = array();
	private $_curRow = 0; //int
	protected $_type = null;

	// <editor-fold desc="Implementation of IIterator interface based on GenericIterator">

	public function hasNext()
	{
		return ($this->_curRow < $this->count());
	}

	public function moveNext()
	{
		if ($this->hasNext())
		{
			$singleRow = new SingleRow($this->_items[$this->_curRow++]);
			return $singleRow;
		}
		else
		{
			throw new IteratorException("Cannot move to next item");
		}
	}

	public function count()
	{
		return count($this->_items);
	}

	function key()
	{
		return $this->_curRow;
	}

	// </editor-fold>

	public function __construct($it = null, $type = 'BaseModel')
	{
		if (!is_null($it))
		{
			if (!($it instanceof IteratorInterface))
			{
				throw new InvalidArgumentException("You have to pass an IIterator class");
			}

			// Check if the object is an instance of BaseModel
			$tObj = new $type();
			if (!($tObj instanceof BaseModel))
			{
				throw new InvalidArgumentException("You have to pass a BaseModel descendant as type");
			}

			// Save the type to be used in the future
			$this->_type = $type;

			foreach ($it as $singleRow)
			{
				$item = new $type($singleRow);
				$this->addItem($item);
			}
		}
	}

	// <editor-fold desc="Implementation of BaseModelCollection methods">
	public function getItems()
	{
		return $this->_items;
	}

	public function addItem($item)
	{
		$this->_items[] = $item;
	}

	public function getItem($index)
	{
		if (array_key_exists($index, $this->_items))
		{
			return $this->_items[$index];
		}
		else
		{
			throw new IteratorException('The index is out of range.');
		}
	}

	// </editor-fold>
}

