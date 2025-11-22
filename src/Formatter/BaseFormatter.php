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
    #[\Override]
    abstract public function raw(): mixed;

    /**
     * @inheritDoc
     */
    #[\Override]
    abstract public function toText(): string|false;

    /**
     * @inheritDoc
     */
    #[\Override]
    public function saveToFile(string $filename): void
    {
        if (empty($filename)) {
            throw new InvalidArgumentException("Filename cannot be empty");
        }
        $text = $this->toText();
        if ($text === false) {
            throw new InvalidArgumentException("Unable to convert to text");
        }
        file_put_contents($filename, $text);
    }

    /**
     * @param GenericIterator|RowInterface $object
     */
    public function __construct(GenericIterator|RowInterface $object)
    {
        $this->object = $object;
    }
}