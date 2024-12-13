<?php

namespace ByJG\AnyDataset\Core;

use ReturnTypeWillChange;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 */
class AnyIterator extends GenericIterator
{

    /**
     * Row Elements
     * @var array
     */
    private array $list;

    /**
     * Current row number
     * @var int
     */
    private int $curRow;

    /**
     * Iterator constructor
     *
     * @param Row[] $list
     */
    public function __construct(array $list)
    {
        $this->curRow = 0;
        $this->list = $list;
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function key(): mixed
    {
        return $this->curRow;
    }

    /**
     * @inheritDoc
     */
    #[ReturnTypeWillChange]
    public function current(): mixed
    {
        return $this->list[$this->curRow] ?? null;
    }

    #[ReturnTypeWillChange]
    public function next(): void
    {
        $this->curRow++;
    }

    #[ReturnTypeWillChange]
    public function valid(): bool
    {
        return ($this->curRow < count($this->list));
    }
}
