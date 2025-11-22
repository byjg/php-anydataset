<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;

class JsonFormatter extends BaseFormatter
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function raw(): mixed
    {
        $text = $this->toText();
        if ($text === false) {
            return false;
        }
        return json_decode($text);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function toText(): string|false
    {
        if ($this->object instanceof GenericIterator) {
            return json_encode($this->object->toArray());
        }
        return json_encode($this->object->toArray());
    }
}