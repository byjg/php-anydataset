<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\Serializer\Exception\InvalidArgumentException;

class JsonFormatter extends BaseFormatter
{
    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function raw(): mixed
    {
        return json_decode($this->toText());
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function toText(): string
    {
        if ($this->object instanceof GenericIterator) {
            return json_encode($this->object->toArray());
        }
        return json_encode($this->object->toArray());
    }
}