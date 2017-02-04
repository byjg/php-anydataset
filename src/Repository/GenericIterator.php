<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\NotImplementedException;
use ByJG\Serializer\DumpToArrayInterface;
use Iterator;

abstract class GenericIterator implements IteratorInterface, Iterator, DumpToArrayInterface
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

    public function key()
    {
        throw new NotImplementedException("Implement this method");
    }

    public function toArray()
    {
        $retArray = [];

        while ($this->hasNext()) {
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
    public function current()
    {
        return $this->moveNext();
    }

    public function rewind()
    {
        // There is no necessary
    }

    public function next()
    {
        // There is no necessary
    }

    public function valid()
    {
        return $this->hasNext();
    }
}
