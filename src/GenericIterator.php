<?php

namespace ByJG\AnyDataset\Core;

use Iterator;

abstract class GenericIterator implements IteratorInterface, Iterator
{

    /**
     * @inheritDoc
     */
    abstract public function hasNext();

    /**
     * @inheritDoc
     */
    abstract public function moveNext();

    /**
     * @inheritDoc
     */
    abstract public function count();

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    abstract public function key();

    /**
     * @inheritDoc
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     */
    public function toArray($fields = [])
    {
        $retArray = [];

        foreach ($this as $singleRow) {
            $retArray[] = $singleRow->toArray($fields);
        }
    
        return $retArray;
    }

    /* ------------------------------------- */
    /* PHP 5 Specific functions for Iterator */
    /* ------------------------------------- */

    /**
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->moveNext();
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        // There is no necessary
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        // There is no necessary
    }

    /**
     * @inheritDoc
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->hasNext();
    }
}
