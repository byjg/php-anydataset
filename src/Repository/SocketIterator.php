<?php

namespace ByJG\AnyDataset\Repository;

class SocketIterator extends GenericIterator
{
	private $_colsep = null;
	private $_rowsep = null;
	private $_fields = null; //Array
	private $_handle = null;

	private $_rows = null;
	private $_current = 0;

    /**
     *
     * @param type $handle
     * @param type $fieldnames
     * @param type $rowsep
     * @param type $colsep
     */
	public function __construct($handle, $fieldnames, $rowsep, $colsep)
	{
		$this->_rowsep = $rowsep;
		$this->_colsep = $colsep;
		$this->_fields = $fieldnames;
		$this->_handle = $handle;

		$header = true;
		while (!feof($this->_handle) && $header)
		{
			$x = fgets($this->_handle);
			$header = (trim($x) != "");
		}

		$linha = "";
		while (!feof($this->_handle))
		{
			$x = fgets($this->_handle, 4096);
			if ((trim($x) != "") && (strpos($x, $this->_colsep)>0) )
			{
				$linha .= $x;
			}
			$header = (trim($x) != "");
		}

		$this->_rows = array();
		$rowsaux = preg_split("/" . $this->_rowsep . "/", $linha);
		sort($rowsaux);
		foreach($rowsaux as $key=>$value)
		{
			$colsaux = preg_split("/" . $this->_colsep . "/", $value);
			if (sizeof($colsaux) == sizeof($fieldnames))
			{
				$this->_rows[] = $value;
			}
		}

		fclose($this->_handle);
	}

	public function count()
	{
		return sizeof($this->_rows);
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if ($this->_current < $this->count())
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	*@access public
	*@return SingleRow
	*/
	public function moveNext()
	{
		$cols = preg_split("/" . $this->_colsep . "/", $this->_rows[$this->_current]);
		$this->_current++;

		$sr = new SingleRow();
		$cntFields = count($this->_fields);
		for($i=0; $i < $cntFields; $i++)
		{
			$sr->addField(strtolower($this->_fields[$i]), $cols[$i]);
		}
		return 	$sr;
	}

 	function key()
 	{
 		return $this->_current;
 	}
}

