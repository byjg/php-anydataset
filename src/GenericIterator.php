<?php

namespace ByJG\AnyDataset\Core;

use Iterator;
use ReturnTypeWillChange;

/**
 * @psalm-suppress MissingTemplateParam
 */
abstract class GenericIterator implements IteratorInterface, Iterator
{
    public function hasNext(): bool
    {
        return $this->valid();
    }

    public function moveNext(): RowInterface|null
    {
        $row = $this->current();
        $this->next();
        return $row;
    }

    /**
     * @inheritDoc
     * @param array $fields
     * @return array
     */
    public function toArray(array $fields = []): array
    {
        $retArray = [];

        foreach ($this as $singleRow) {
            $retArray[] = $singleRow->toArray($fields);
        }
    
        return $retArray;
    }

    /* --------------------------------------------- */
    /* PHP Specific functions for Iterator interface */
    /* --------------------------------------------- */

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    abstract public function key(): mixed;

    /**
     * @return mixed
     */
    #[ReturnTypeWillChange]
    abstract public function current(): mixed;

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function rewind(): void
    {
        // Do nothing
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    abstract public function next(): void;

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    abstract public function valid(): bool;
}
