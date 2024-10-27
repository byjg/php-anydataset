<?php

namespace ByJG\AnyDataset\Core;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 */
class AnyIterator extends GenericIterator
{

    /**
     * Row Elements
     * @var array
     */
    private $list;

    /**
     * Current row number
     * @var int
     */
    private $curRow; //int

    /**
     * Iterator constructor
     *
     * @param Row[] $list
     */
    public function __construct($list)
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
    public function moveNext(): Row|null
    {
        if (!$this->hasNext()) {
            return null;
        }
        return $this->list[$this->curRow++];
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->curRow;
    }

    /**
     * @param IteratorFilter $filter
     * @return AnyIterator
     */
    public function withFilter(IteratorFilter $filter)
    {
        return new AnyIterator($filter->match($this->list));
    }
}
