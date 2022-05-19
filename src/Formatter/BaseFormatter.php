<?php

namespace ByJG\AnyDataset\Core\Formatter;

use \ByJG\AnyDataset\Core\AnyDataset;
use \ByJG\AnyDataset\Core\Row;
use InvalidArgumentException;

abstract class BaseFormatter
{
    /**
     * @var AnyDataset
     */
    protected $object;

    abstract public function raw();

    abstract public function toText();

    public function saveToFile($filename)
    {
        file_put_contents($filename, $this->toText());
    }

    /**
     * $object AnyDataset|Row
     */
    public function __construct($object)
    {
        if (!($object instanceof AnyDataset) && !($object instanceof Row)) {
            throw new InvalidArgumentException("Constructor must have an AnyDataset or Row instance in the argument");
        }
        $this->object = $object;
    }
}