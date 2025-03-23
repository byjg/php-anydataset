<?php

namespace ByJG\AnyDataset\Core;

use ReturnTypeWillChange;

/**
 * @psalm-suppress MissingTemplateParam
 */
abstract class GenericIterator implements IteratorInterface
{
    /**
     * @inheritDoc
     * @param array $fields
     * @return array
     */
    #[\Override]
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
    #[\Override]
    #[ReturnTypeWillChange]
    abstract public function key(): mixed;

    /**
     * @return mixed
     */
    #[\Override]
    #[ReturnTypeWillChange]
    abstract public function current(): mixed;

    /**
     * @inheritDoc
     */
    #[\Override]
    #[ReturnTypeWillChange]
    public function rewind(): void
    {
        // Do nothing
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    #[ReturnTypeWillChange]
    abstract public function next(): void;

    /**
     * @inheritDoc
     */
    #[\Override]
    #[ReturnTypeWillChange]
    abstract public function valid(): bool;
}
