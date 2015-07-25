<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\IteratorException;

class SQLRelayIterator extends GenericIterator
{
	const RECORD_BUFFER = 50;
	private $_rowBuffer;
	protected $_currentRow = 0;
	protected $_moveNextRow = 0;

	/**
	*@var SQLRelay Cursos
	*/
	private $_cursor;

    /**
     *
     * @param type $cursor
     */
	public function __construct($cursor)
	{
		$this->_cursor = $cursor;
		$this->_rowBuffer = array();
	}

	/**
	* @return int
	*/
	public function count()
	{
		return sqlrcur_rowCount($this->_cursor);
	}

	/**
	* @return bool
	*/
	public function hasNext()
	{
        if (count($this->_rowBuffer) >= SQLRelayIterator::RECORD_BUFFER)
        {
            return true;
        }
        else if (is_null($this->_cursor))
		{
			return (count($this->_rowBuffer) > 0);
		}
		else if ($this->_currentRow < $this->count())
		{
			$sr = new SingleRow();

			$colCount = sqlrcur_colCount($this->_cursor);
      		for ($col=0; $col< $colCount; $col++)
      		{
      			$fieldName = strtolower(sqlrcur_getColumnName($this->_cursor, $col) );
      			$value = sqlrcur_getField($this->_cursor, $this->_currentRow, $col);

      			if (is_null($value))
      			{
      				$sr->addField($fieldName, "");
      			}
      			elseif (is_object($value))
      			{
      				$sr->addField($fieldName, "[OBJECT]");
      			}
				else
				{
					$value = AnyDataset::fixUTF8($value);
					$sr->addField($fieldName, $value);
				}
			}

			$this->_currentRow++;

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
			sqlrcur_free($this->_cursor);
			$this->_cursor = null;

			return (count($this->_rowBuffer) > 0);
		}
	}

	public function __destruct()
	{
		if (!is_null($this->_cursor))
		{
			@sqlrcur_free($this->_cursor);
			$this->_cursor = null;
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
			$this->_moveNextRow++;
			return $sr;
		}
	}

	function key()
 	{
 		return $this->_moveNextRow;
 	}
}

