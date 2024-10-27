<?php

namespace ByJG\AnyDataset\Core;

interface IteratorInterface
{

    /**
     * Check if exists more records.
     * 
     * @return bool Return True if is possible get one or more records.
     */
    public function hasNext(): bool;

    /**
     * Get the next record.Return a Row object
     * 
     * @return Row|null
     */
    public function moveNext(): Row|null;

    /**
     * Get the record count. Some implementations may have return -1.
     *
     * @return int
     */
    public function count(): int;

    /**
     * Get an array of the iterator
     * 
     * @param array $fields
     * @return array
     */
    public function toArray(array $fields = []): array;
}
