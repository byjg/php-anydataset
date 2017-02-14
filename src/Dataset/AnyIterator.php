<?php

namespace ByJG\AnyDataset\Dataset;

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
     * @param SingleRow[] $list
     */

    public function __construct($list)
    {
        $this->curRow = 0;
        $this->list = $list;
    }

    /**
     * How many elements have
     * @return int
     */
    public function count()
    {
        return sizeof($this->list);
    }

    /**
     * Ask the Iterator is exists more rows. Use before moveNext method.
     * @return bool True if exist more rows, otherwise false
     */
    public function hasNext()
    {
        return ($this->curRow < $this->count());
    }

    /**
     * Return the next row.
     * @return SingleRow
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            return null;
        }
        return $this->list[$this->curRow++];
    }

    public function key()
    {
        return $this->curRow;
    }
}
