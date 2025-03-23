<?php

namespace ByJG\AnyDataset\Core;

use Iterator;

interface IteratorInterface extends Iterator
{
    /**
     * Get an array of the iterator
     * 
     * @param array $fields
     * @return array
     */
    public function toArray(array $fields = []): array;
}
