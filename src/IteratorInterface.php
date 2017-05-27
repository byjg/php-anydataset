<?php

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Dataset\Row;

interface IteratorInterface
{

    /**
     * @desc Check if exists more records.
     * @return bool Return True if is possible get one or more records.
     */
    public function hasNext();

    /**
     * @desc Get the next record.Return a Row object
     * @return Row
     */
    public function moveNext();

    /**
     * @desc Get the record count. Some implementations may have return -1.
     *
     */
    public function count();

    /**
     * Get an array of the iterator
     */
    public function toArray();
}
