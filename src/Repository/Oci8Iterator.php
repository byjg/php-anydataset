<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\IteratorException;

class Oci8Iterator extends GenericIterator
{

    const RECORD_BUFFER = 50;

    private $_rowBuffer;
    protected $_currentRow = 0;
    protected $_moveNextRow = 0;

    /**
     * @var resource Cursor
     */
    private $_cursor;

    /**
     *
     * @param resource $cursor
     */
    public function __construct($cursor)
    {
        $this->_cursor = $cursor;
        $this->_rowBuffer = array();
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
        if (count($this->_rowBuffer) >= Oci8Iterator::RECORD_BUFFER) {
            return true;
        } else if (is_null($this->_cursor)) {
            return (count($this->_rowBuffer) > 0);
        } else {
            $row = oci_fetch_array($this->_cursor, OCI_ASSOC + OCI_RETURN_NULLS);
            if ($row) {
                $row = array_change_key_case($row, CASE_LOWER);
                $sr = new SingleRow($row);

                $this->_currentRow++;

                // Enfileira o registo
                array_push($this->_rowBuffer, $sr);
                // Traz novos até encher o Buffer
                if (count($this->_rowBuffer) < DbIterator::RECORD_BUFFER) {
                    $this->hasNext();
                }
                return true;
            } else {
                oci_free_statement($this->_cursor);
                $this->_cursor = null;
                return (count($this->_rowBuffer) > 0);
            }
        }
    }

    public function __destruct()
    {
        if (!is_null($this->_cursor)) {
            oci_free_statement($this->_cursor);
            $this->_cursor = null;
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
            $sr = array_shift($this->_rowBuffer);
            $this->_moveNextRow++;
            return $sr;
        }
    }

    public function key()
    {
        return $this->_moveNextRow;
    }
}
