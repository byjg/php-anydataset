<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\Row;
use InvalidArgumentException;

abstract class BaseFormatter implements FormatterInterface
{
    /**
     * @var GenericIterator|Row
     */
    protected Row|GenericIterator $object;

    /**
     * @inheritDoc
     */
    abstract public function raw(): mixed;

    /**
     * @inheritDoc
     */
    abstract public function toText(): string;

    /**
     * @inheritDoc
     */
    public function saveToFile(string $filename): void
    {
        if (empty($filename)) {
            throw new InvalidArgumentException("Filename cannot be empty"); 
        }
        file_put_contents($filename, $this->toText());
    }

    /**
     * @param GenericIterator|Row $object
     */
    public function __construct(GenericIterator|Row $object)
    {
        if (!($object instanceof GenericIterator) && !($object instanceof Row)) {
            throw new InvalidArgumentException("Constructor must have a GenericIterator or Row instance in the argument");
        }
        $this->object = $object;
    }
}