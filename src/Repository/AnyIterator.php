<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\SingleRow;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 */
class AnyIterator extends GenericIterator
{

	/**
	*@desc Row Elements
	*@access private
	*@var array
	*/
	private $_list;

	/**
	*@access private
	*@var int
	*@desc Current row number
	*/
	private	$_curRow;//int

	/**
	*@access public
	*@param \DOMNodeList $list \DOMNodeList
	*@return void
	*@desc Iterator constructor
	*/
	public function __construct($list)
	{
		$this->_curRow = 0;
		$this->_list = $list;
	}

	/**
	*@access public
	*@return int
	*@desc How many elements have
	*/
	public function Count()
	{
		return sizeof($this->_list);
	}

	/**
	*@access public
	*@return bool - True if exist more rows, otherwise false
	*@desc Ask the Iterator is exists more rows. Use before moveNext method.
	*/
	public function hasNext()
	{
		return ($this->_curRow < $this->Count());
	}

	/**
	*@access public
	*@return SingleRow
	*@desc Return the next row.
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			return null;
		}
		else
		{
			return $this->_list[$this->_curRow++];
		}
	}

 	function key()
 	{
 		return $this->_curRow;
 	}

}
?>