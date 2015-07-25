<?php

namespace ByJG\AnyDataset\Repository;

use SparQL\Result;
use ByJG\AnyDataset\Exception\IteratorException;

class SparQLIterator extends GenericIterator
{
	/**
	 * @var Result
	 */
	private $_sparqlQuery;

	/**
	 * Enter description here...
	 *
	 * @var int
	 */
	private $_current = 0;

	public function __construct(Result $sparqlQuery)
	{
		$this->_sparqlQuery = $sparqlQuery;

		$this->_current = 0;
	}

	public function Count()
	{
		return ($this->_sparqlQuery->numRows());
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

		if ($row = $this->_sparqlQuery->fetchArray())
		{
			$sr = new SingleRow($row);
			$this->_current++;
			return 	$sr;
		}
		else
		{
			throw new IteratorException("No more records. Unexpected behavior.");
		}

	}

 	function key()
 	{
 		return $this->_current;
 	}

}

