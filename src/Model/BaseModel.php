<?php

namespace ByJG\AnyDataset\Model;

abstract class BaseModel extends BinderObject
{

    /**
     * Construct a model and optionally can set (bind) your properties base and the attribute matching from SingleRow, IteratorInterface
     * @param Object $object
     */
    public function __construct($object = null)
    {
        if (!is_null($object)) {
            $this->bind($object);
        }
    }

}
