<?php

namespace ByJG\AnyDataset\Core;

interface IteratorInterface
{

    /**
     * Check if exists more records.
     * 
     * @return bool Return True if is possible get one or more records.
     */
    public function hasNext();

    /**
     * Get the next record.Return a Row object
     * 
     * @return Row|null
     */
    public function moveNext();

    /**
     * Get the record count. Some implementations may have return -1.
     *
     * @return int
     */
    public function count();

    /**
     * Get an array of the iterator
     * 
     * @param array $fields
     * @return array
     */
    public function toArray($fields = []);
}
