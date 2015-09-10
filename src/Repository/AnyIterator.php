<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Repository\GenericIterator;
use ByJG\AnyDataset\Repository\SingleRow;
use DOMNodeList;

/**
 * Iterator class is a structure used to navigate forward in a AnyDataset structure.
 */
class AnyIterator extends GenericIterator
{

    /**
     * Row Elements
     * @var array
     */
    private $_list;

    /**
     * Current row number
     * @var int
     */
    private $_curRow; //int

    /**
     * Iterator constructor
     * @param DOMNodeList $list \DOMNodeList
     * @return void
     */

    public function __construct($list)
    {
        $this->_curRow = 0;
        $this->_list = $list;
    }

    /**
     * How many elements have
     * @return int
     */
    public function count()
    {
        return sizeof($this->_list);
    }

    /**
     * Ask the Iterator is exists more rows. Use before moveNext method.
     * @return bool True if exist more rows, otherwise false
     */
    public function hasNext()
    {
        return ($this->_curRow < $this->count());
    }

    /**
     * Return the next row.
     * @return SingleRow
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            return null;
        } else {
            return $this->_list[$this->_curRow++];
        }
    }

    function key()
    {
        return $this->_curRow;
    }
}
