<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;
use ForceUTF8\Encoding;
use PDO;
use PDOStatement;

class DbIterator extends GenericIterator
{

    const RECORD_BUFFER = 50;

    private $rowBuffer;
    private $currentRow = 0;

    /**
     * @var PDOStatement
     */
    private $recordset;

    /**
     * @param PDOStatement $recordset
     */
    public function __construct($recordset)
    {
        $this->recordset = $recordset;
        $this->rowBuffer = array();
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->recordset->rowCount();
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        if (count($this->rowBuffer) >= DbIterator::RECORD_BUFFER) {
            return true;
        }

        if (is_null($this->recordset)) {
            return (count($this->rowBuffer) > 0);
        }

        $rowArray = $this->recordset->fetch(PDO::FETCH_ASSOC);
        if (!empty($rowArray)) {
            foreach ($rowArray as $key => $value) {
                if (is_null($value)) {
                    $rowArray[$key] = "";
                } elseif (is_object($value)) {
                    $rowArray[$key] = "[OBJECT]";
                } else {
                    $rowArray[$key] = Encoding::toUTF8($value);
                }
            }
            $singleRow = new Row($rowArray);

            // Enfileira o registo
            array_push($this->rowBuffer, $singleRow);
            // Traz novos até encher o Buffer
            if (count($this->rowBuffer) < DbIterator::RECORD_BUFFER) {
                $this->hasNext();
            }

            return true;
        }

        $this->recordset->closeCursor();
        $this->recordset = null;

        return (count($this->rowBuffer) > 0);
    }

    /**
     * @return Row
     * @throws IteratorException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        } else {
            $singleRow = array_shift($this->rowBuffer);
            $this->currentRow++;
            return $singleRow;
        }
    }

    public function key()
    {
        return $this->currentRow;
    }
}
