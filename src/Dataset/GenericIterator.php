<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\IteratorInterface;
use ByJG\Serializer\DumpToArrayInterface;
use Iterator;

abstract class GenericIterator implements IteratorInterface, Iterator, DumpToArrayInterface
{

    abstract public function hasNext();

    abstract public function moveNext();

    abstract public function count();

    abstract public function key();

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
