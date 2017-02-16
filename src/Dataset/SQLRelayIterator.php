<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;

class SQLRelayIterator extends GenericIterator
{

    const RECORD_BUFFER = 50;

    private $rowBuffer;
    protected $currentRow = 0;
    protected $moveNextRow = 0;

    /**
     * @var resource
     */
    private $cursor;

    /**
     *
     * @param resource $cursor
     */
    public function __construct($cursor)
    {
        $this->cursor = $cursor;
        $this->rowBuffer = array();
    }

    /**
     * @return int
     */
    public function count()
    {
        return sqlrcur_rowCount($this->cursor);
    }

    /**
     * @return bool
     */
    public function hasNext()
    {
        if (count($this->rowBuffer) >= SQLRelayIterator::RECORD_BUFFER) {
            return true;
        }

        if (is_null($this->cursor)) {
            return (count($this->rowBuffer) > 0);
        }

        if ($this->currentRow < $this->count()) {
            $sr = new SingleRow();

            $colCount = sqlrcur_colCount($this->cursor);
            for ($col = 0; $col < $colCount; $col++) {
                $fieldName = strtolower(sqlrcur_getColumnName($this->cursor, $col));
                $value = sqlrcur_getField($this->cursor, $this->currentRow, $col);

                if (is_null($value)) {
                    $sr->addField($fieldName, "");
                } elseif (is_object($value)) {
                    $sr->addField($fieldName, "[OBJECT]");
                } else {
                    $value = AnyDataset::fixUTF8($value);
                    $sr->addField($fieldName, $value);
                }
            }

            $this->currentRow++;

            // Enfileira o registo
            array_push($this->rowBuffer, $sr);
            // Traz novos até encher o Buffer
            if (count($this->rowBuffer) < DbIterator::RECORD_BUFFER) {
                $this->hasNext();
            }

            return true;
        }

        sqlrcur_free($this->cursor);
        $this->cursor = null;

        return (count($this->rowBuffer) > 0);
    }

    public function __destruct()
    {
        if (!is_null($this->cursor)) {
            @sqlrcur_free($this->cursor);
            $this->cursor = null;
        }
    }

    /**
     * @return SingleRow
     * @throws IteratorException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        }

        $sr = array_shift($this->rowBuffer);
        $this->moveNextRow++;
        return $sr;
    }

    public function key()
    {
        return $this->moveNextRow;
    }
}
