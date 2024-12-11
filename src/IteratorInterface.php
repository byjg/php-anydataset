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
     * @return RowInterface|null
     */
    public function moveNext(): RowInterface|null;

    /**
     * Get an array of the iterator
     * 
     * @param array $fields
     * @return array
     */
    public function toArray(array $fields = []): array;
}
