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
     * @return Row
     * @throws \ByJG\AnyDataset\Exception\IteratorException
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        if ($row = $this->sparqlQuery->fetchArray()) {
            $row = new Row($row);
            $this->current++;
            return $row;
        } else {
            throw new IteratorException("No more records. Unexpected behavior.");
        }
    }

    public function key()
    {
        return $this->current;
    }
}
