<?php

namespace ByJG\AnyDataset\Repository;

class TextFileIterator extends GenericIterator
{

    protected $_fields;
    protected $_fieldexpression;
    protected $_handle;
    protected $_current = 0;
    protected $_currentBuffer = "";

    /**
     * @access public
     * @param resource $handle
     * @param array $fields
     * @param string $fieldexpression
     */
    public function __construct($handle, $fields, $fieldexpression)
    {
        $this->_fields = $fields;
        $this->_fieldexpression = $fieldexpression;
        $this->_handle = $handle;

        $this->readNextLine();
    }

    protected function readNextLine()
    {
        if ($this->hasNext()) {
            $buffer = fgets($this->_handle, 4096);
            $this->_currentBuffer = false;

            if (($buffer !== false) && (trim($buffer) != "")) {
                $this->_current++;
                $this->_currentBuffer = $buffer;
            } else $this->readNextLine();
        }
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
        if ($this->_currentBuffer !== false) {
            return true;
        } elseif (!$this->_handle) {
            return false;
        } else {
            if (feof($this->_handle)) {
                fclose($this->_handle);
                $this->_handle = null;
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * @access public
     * @return SingleRow
     */
    public function moveNext()
    {
        if ($this->hasNext()) {
            $cols = preg_split($this->_fieldexpression, $this->_currentBuffer, -1, PREG_SPLIT_DELIM_CAPTURE);

            $sr = new SingleRow();

            for ($i = 0; ($i < sizeof($this->_fields)) && ($i < sizeof($cols)); $i++) {
                $column = $cols[$i];

                if (($i >= sizeof($this->_fields) - 1) || ($i >= sizeof($cols) - 1)) {
                    $column = preg_replace("/(\r?\n?)$/", "", $column);
                }

                $sr->addField(strtolower($this->_fields[$i]), $column);
            }

            $this->readNextLine();
            return $sr;
        } else {
            if ($this->_handle) {
                fclose($this->_handle);
            }
            return null;
        }
    }

    function key()
    {
        return $this->_current;
    }
}
