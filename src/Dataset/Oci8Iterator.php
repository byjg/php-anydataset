<?php

namespace ByJG\AnyDataset\Dataset;

use ByJG\AnyDataset\Exception\IteratorException;

class Oci8Iterator extends GenericIterator
{

    const RECORD_BUFFER = 50;

    private $rowBuffer;
    protected $currentRow = 0;
    protected $moveNextRow = 0;

    /**
     * @var resource Cursor
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
     * @access public
     * @return int
     */
    public function count()
    {
        return -1;
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if (count($this->rowBuffer) >= Oci8Iterator::RECORD_BUFFER) {
            return true;
        }

        if (is_null($this->cursor)) {
            return (count($this->rowBuffer) > 0);
        }

        $row = oci_fetch_array($this->cursor, OCI_ASSOC + OCI_RETURN_NULLS);
        if (!empty($row)) {
            $row = array_change_key_case($row, CASE_LOWER);
            $singleRow = new SingleRow($row);

            $this->currentRow++;

            // Enfileira o registo
            array_push($this->rowBuffer, $singleRow);
            // Traz novos até encher o Buffer
            if (count($this->rowBuffer) < DbIterator::RECORD_BUFFER) {
                $this->hasNext();
            }
            return true;
        }

        oci_free_statement($this->cursor);
        $this->cursor = null;
        return (count($this->rowBuffer) > 0);
    }

    public function __destruct()
    {
        if (!is_null($this->cursor)) {
            oci_free_statement($this->cursor);
            $this->cursor = null;
        }
    }

    /**
     * @return mixed
     * @throws IteratorException
     */
    public function moveNext()
    {
        if (!$this->hasNext()) {
            throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
        } else {
            $sr = array_shift($this->rowBuffer);
            $this->moveNextRow++;
            return $sr;
        }
    }

    public function key()
    {
        return $this->moveNextRow;
    }
}
