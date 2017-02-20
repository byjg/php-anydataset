<?php

namespace ByJG\AnyDataset\Dataset;

class SocketIterator extends GenericIterator
{

    private $colsep = null;
    private $rowsep = null;
    private $fields = null; //Array
    private $handle = null;
    private $rows = null;
    private $current = 0;

    /**
     *
     * @param resource $handle
     * @param array $fieldnames
     * @param string $rowsep
     * @param string $colsep
     */
    public function __construct($handle, $fieldnames, $rowsep, $colsep)
    {
        $this->rowsep = $rowsep;
        $this->colsep = $colsep;
        $this->fields = $fieldnames;
        $this->handle = $handle;

        $header = true;
        while (!feof($this->handle) && $header) {
            $x = fgets($this->handle);
            $header = (trim($x) != "");
        }

        $linha = "";
        while (!feof($this->handle)) {
            $x = fgets($this->handle, 4096);
            if ((trim($x) != "") && (strpos($x, $this->colsep) > 0)) {
                $linha .= $x;
            }
        }

        $this->rows = array();
        $rowsaux = preg_split("/" . $this->rowsep . "/", $linha);
        sort($rowsaux);
        foreach ($rowsaux as $value) {
            $colsaux = preg_split("/" . $this->colsep . "/", $value);
            if (count($colsaux) == count($fieldnames)) {
                $this->rows[] = $value;
            }
        }

        fclose($this->handle);
    }

    public function count()
    {
        return count($this->rows);
    }

    /**
     * @access public
     * @return bool
     */
    public function hasNext()
    {
        if ($this->current < $this->count()) {
            return true;
        }

        return false;
    }

    /**
     * @access public
     * @return SingleRow
     */
    public function moveNext()
    {
        $cols = preg_split("/" . $this->colsep . "/", $this->rows[$this->current]);
        $this->current++;

        $sr = new SingleRow();
        $cntFields = count($this->fields);
        for ($i = 0; $i < $cntFields; $i++) {
            $sr->addField(strtolower($this->fields[$i]), $cols[$i]);
        }
        return $sr;
    }

    public function key()
    {
        return $this->current;
    }
}
