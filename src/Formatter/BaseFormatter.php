<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use ByJG\AnyDataset\Core\RowInterface;
use InvalidArgumentException;

abstract class BaseFormatter implements FormatterInterface
{
    /**
     * @var GenericIterator|RowInterface
     */
    protected RowInterface|GenericIterator $object;

    /**
     * @inheritDoc
     */
    abstract public function raw(): mixed;

    /**
     * @inheritDoc
     */
    abstract public function toText(): string|false;

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
     * @param GenericIterator|RowInterface $object
     */
    public function __construct(GenericIterator|RowInterface $object)
    {
        $this->object = $object;
    }
}