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
    public function count(): int
    {
        return count($this->list);
    }

    /**
     * @inheritDoc
     */
    public function hasNext(): bool
    {
        return ($this->curRow < $this->count());
    }

    /**
     * @inheritDoc
     */
    public function moveNext(): RowInterface|null
    {
        if (!$this->hasNext()) {
            return null;
        }
        return $this->list[$this->curRow++];
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

    /**
     * @param IteratorFilter $filter
     * @return AnyIterator
     */
    public function withFilter(IteratorFilter $filter): AnyIterator
    {
        return new AnyIterator($filter->match($this->list));
    }
}
