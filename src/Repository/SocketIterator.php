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
		//Debug::PrintValue("Entrou Header");
		while (!feof($this->_handle) && $header)
		{
			$x = fgets($this->_handle);
			//Debug::PrintValue($x);
			$header = (trim($x) != "");
		}
		//Debug::PrintValue("Saiu Header");

		$linha = "";
		//Debug::PrintValue("INicio Leitura");
		$rowseptemp = str_replace("\\", "", $this->_rowsep);
		while (!feof($this->_handle))
		{
			$x = fgets($this->_handle, 4096);
			if ((trim($x) != "") && (strpos($x, $this->_colsep)>0) )
			{
				//Debug::PrintValue($x);
				$linha .= $x;
			}
			$header = (trim($x) != "");
		}
		//Debug::PrintValue("Fim Leitura");

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

	public function Count()
	{
		return sizeof($this->_rows);
	}

	/**
	*@access public
	*@return bool
	*/
	public function hasNext()
	{
		if ($this->_current < $this->Count())
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
		for($i=0;$i<sizeof($this->_fields); $i++)
		{
			$sr->AddField(strtolower($this->_fields[$i]), $cols[$i]);
			//Debug::PrintValue(strtolower($this->_fields[$i]), $cols[$i]);
		}
		return 	$sr;
	}

 	function key()
 	{
 		return $this->_current;
 	}
}
?>
