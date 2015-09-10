<?php

namespace ByJG\AnyDataset\Repository;

interface IteratorInterface
{

    /**
     * @desc Check if exists more records.
     * @return bool Return True if is possible get one or more records.
     */
    function hasNext();

    /**
     * @desc Get the next record.Return a SingleRow object
     * @return SingleRow
     */
    function moveNext();

    /**
     * @desc Get the record count. Some implementations may have return -1.
     *
     */
    function Count();

    /**
     * Get an array of the iterator
     */
    function toArray();
}
