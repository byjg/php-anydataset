<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\IteratorException;
use ForceUTF8\Encoding;
use PDO;

class DBIterator extends GenericIterator
{
	const RECORD_BUFFER = 50;
	private $_rowBuffer;

	private $_currentRow = 0;

	/**
	* @var \PDOStatement
	*/
	private $_rs;

	/**
	* @param PDOStatement $recordset
	* @return void
	*/
	public function __construct($recordset)
	{
		$this->_rs = $recordset;
		$this->_rowBuffer = array();
	}

	/**
	* @return int
	*/
	public function Count()
	{
		return $this->_rs->rowCount();
	}

	/**
	* @return bool
	*/
	public function hasNext()
	{
        if (count($this->_rowBuffer) >= DBIterator::RECORD_BUFFER)
        {
            return true;
        }
        else if (is_null($this->_rs))
		{
			return (count($this->_rowBuffer) > 0);
		}
		else if ($row = $this->_rs->fetch(PDO::FETCH_ASSOC))
		{
			foreach ($row as $key=>$value)
			{
      			if (is_null($value))
      			{
      				$row[$key]  = "";
      			}
      			elseif (is_object($value))
      			{
      				$row[$key] = "[OBJECT]";
      			}
      			else
				{
					$row[$key] = Encoding::toUTF8($value);
				}
			}
			$sr = new SingleRow($row);

			// Enfileira o registo
			array_push($this->_rowBuffer, $sr);
			// Traz novos até encher o Buffer
			if (count($this->_rowBuffer) < DBIterator::RECORD_BUFFER)
			{
				$this->hasNext();
			}

			return true;
		}
		else
		{
			$this->_rs->closeCursor();
            $this->_rs = null;

			return (count($this->_rowBuffer) > 0);
		}
	}

	/**
	* @return SingleRow
	*/
	public function moveNext()
	{
		if (!$this->hasNext())
		{
			throw new IteratorException("No more records. Did you used hasNext() before moveNext()?");
		}
		else
		{
			$sr = array_shift($this->_rowBuffer);
			$this->_currentRow++;
			return $sr;
		}
	}

 	function key()
 	{
 		return $this->_currentRow;
 	}
}

