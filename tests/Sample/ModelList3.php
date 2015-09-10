<?php

namespace Tests\Sample;

use Exception;

/**
 * @Xmlnuke:NodeName ModelList
 */
class ModelList3
{

    protected $_collection = array();

    /**
     * Add ModelGetter to a List
     * @param ModelGetter $obj Description
     */
    public function addItem($obj)
    {
        if (!($obj instanceof ModelGetter)) {
            throw new Exception('Invalid type');
        } else {
            $this->_collection[] = $obj;
        }
    }

    /**
     * Retrieve an array of ModelGetter, dont create it and force the descendats named List
     * @Xmlnuke:DontCreateNode List
     */
    public function getCollection()
    {
        if (count($this->_collection) > 0) {
            return $this->_collection;
        } else {
            return null;
        }
    }
}
