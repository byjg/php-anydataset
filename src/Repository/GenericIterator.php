<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\NotImplementedException;
use Iterator;

abstract class GenericIterator implements IteratorInterface, Iterator, \ByJG\AnyDataset\Model\DumpToArrayInterface
{
	public function hasNext()
	{
		throw new NotImplementedException("Implement this method");
	}

	public function moveNext()
	{
		throw new NotImplementedException("Implement this method");
	}

	public function count()
	{
		throw new NotImplementedException("Implement this method");
	}

	function key()
	{
		throw new NotImplementedException("Implement this method");
	}

    public function toArray()
    {
        $retArray = [];

        while ($this->hasNext())
        {
            $singleRow = $this->moveNext();
            $retArray[] = $singleRow->toArray();
        }

        return $retArray;
    }

	/* ------------------------------------- */
	/* PHP 5 Specific functions for Iterator */
	/* ------------------------------------- */

	/**
	 * @return SingleRow
	 */
 	function current()
 	{
 		return $this->moveNext();
  	}

	function rewind ()
	{
		// There is no necessary
	}

 	function next ()
	{
		// There is no necessary
	}

	function valid()
	{
		return $this->hasNext();
	}

}
