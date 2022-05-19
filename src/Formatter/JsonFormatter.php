<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\AnyDataset;

class JsonFormatter extends BaseFormatter
{
    public function raw()
    {
        return json_decode($this->toText());
    }


    public function toText()
    {
        if ($this->object instanceof AnyDataset) {
            return json_encode($this->object->getIterator()->toArray());
        }
        return json_encode($this->object->toArray());
    }
}