<?php

namespace ByJG\AnyDataset\Core\Formatter;

use ByJG\AnyDataset\Core\GenericIterator;
use \ByJG\AnyDataset\Core\Row;
use InvalidArgumentException;

abstract class BaseFormatter implements FormatterInterface
{
    /**
     * @var GenericIterator|Row
     */
    protected $object;

    abstract public function raw();

    abstract public function toText();

    public function saveToFile($filename)
    {
        if (empty($filename)) {
            throw new InvalidArgumentException("Filename cannot be empty"); 
        }
        file_put_contents($filename, $this->toText());
    }

    /**
     * $object AnyDataset|Row
     */
    public function __construct($object)
    {
        if (!($object instanceof GenericIterator) && !($object instanceof Row)) {
            throw new InvalidArgumentException("Constructor must have a GenericIterator or Row instance in the argument");
        }
        $this->object = $object;
    }
}