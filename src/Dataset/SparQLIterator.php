<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;
use SparQL\Result;

class SparQLIterator extends GenericIterator
{

    /**
     * @var Result
     */
    private $sparqlQuery;

    /**
     * Enter description here...
     *
     * @var int
     */
    private $current = 0;

    public function __construct(Result $sparqlQuery)
    {
        $this->sparqlQuery = $sparqlQuery;

        $this->current = 0;
    }

    public function count()
    {
        return ($this->sparqlQuery->numRows());
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
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

        if ($row = $this->sparqlQuery->fetchArray()) {
            $sr = new SingleRow($row);
            $this->current++;
            return $sr;
        } else {
            throw new IteratorException("No more records. Unexpected behavior.");
        }
    }

    public function key()
    {
        return $this->current;
    }
}
