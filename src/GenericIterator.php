<?php

namespace ByJG\AnyDataset\Core;

use ByJG\AnyDataset\Core\Exception\NotFoundException;
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

    /**
     * Return the underlying entities for each row in the iterator.
     */
    #[\Override]
    public function toEntities(): array
    {
        $retArray = [];

        foreach ($this as $singleRow) {
            $retArray[] = $singleRow->entity();
        }

        return $retArray;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function first(): mixed
    {
        $this->rewind();
        if (!$this->valid()) {
            return null;
        }
        return $this->current()->entity();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function firstOrFail(): mixed
    {
        $this->rewind();
        if (!$this->valid()) {
            throw new NotFoundException("No results found in iterator");
        }
        return $this->current()->entity();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function exists(): bool
    {
        $this->rewind();
        return $this->valid();
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function existsOrFail(): bool
    {
        if (!$this->exists()) {
            throw new NotFoundException("Iterator is empty");
        }
        return true;
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
