<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;
use SparQL\Result;

class SparQLIterator extends GenericIterator
{

    /**
     * @var Result
     */
    private $_sparqlQuery;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $_current = 0;

    public function __construct(Result $sparqlQuery)
    {
        $this->_sparqlQuery = $sparqlQuery;

        $this->_current = 0;
    }

    public function count()
    {
        return ($this->_sparqlQuery->numRows());
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->_current < $this->count()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @access public
     * @return SingleRow
     * @throws IteratorException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        if ($row = $this->_sparqlQuery->fetchArray()) {
            $sr = new SingleRow($row);
            $this->_current++;
            return $sr;
        } else {
            throw new IteratorException("No more records. Unexpected behavior.");
        }
    }

    public function key()
    {
        return $this->_current;
    }
}
