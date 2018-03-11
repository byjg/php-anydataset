<?php

namespace ByJG\AnyDataset\Dataset;

use InvalidArgumentException;

class ArrayDatasetIterator extends GenericIterator
{

    /**
     * @var array
     */
    protected $rows;

    /**
     * Enter description here...
     *
     * @var array
     */
    protected $keys;

    /**
      /* @var int
     */
    protected $currentRow;

    /**
     * @param $rows
     */
    public function __construct($rows)
    {
        if (!is_array($rows)) {
            throw new InvalidArgumentException("ArrayDatasetIterator must receive an array");
        }
        $this->currentRow = 0;
        $this->rows = $rows;
        $this->keys = array_keys($rows);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->rows);
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        return ($this->currentRow < $this->count());
    }

    /**
     * @return Row
     * @throws \ByJG\Serializer\Exception\InvalidArgumentException
     * @throws \ByJG\Util\Exception\XmlUtilException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            return null;
        }

        $key = $this->keys[$this->currentRow];
        $cols = $this->rows[$key];

        $any = new AnyDataset();
        $any->appendRow();
        $any->addField("__id", $this->currentRow);
        $any->addField("__key", $key);
        foreach ($cols as $key => $value) {
            $any->addField(strtolower($key), $value);
        }
        $iterator = $any->getIterator(null);
        $singleRow = $iterator->moveNext();
        $this->currentRow++;
        return $singleRow;
    }

    public function key()
    {
        return $this->currentRow;
    }
}
