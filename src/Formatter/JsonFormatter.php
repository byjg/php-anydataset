<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;

class JsonFormatter extends BaseFormatter
{
    /**
     * @inheritDoc
     */
    public function raw()
    {
        return json_decode($this->toText());
    }

    /**
     * @inheritDoc
     */
    public function toText()
    {
        if ($this->object instanceof GenericIterator) {
            return json_encode($this->object->toArray());
        }
        return json_encode($this->object->toArray());
    }
}