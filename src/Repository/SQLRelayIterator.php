<?php
/*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*  Copyright:
*
*  XMLNuke: A Web Development Framework based on XML.
*
*  Main Specification: Joao Gilberto Magalhaes, joao at byjg dot com
*  PHP Implementation: Joao Gilberto Magalhaes
*
*  This file is part of XMLNuke project. Visit http://www.xmlnuke.com
*  for more information.
*
*  This program is free software; you can redistribute it and/or
*  modify it under the terms of the GNU General Public License
*  as published by the Free Software Foundation; either version 2
*  of the License, or (at your option) any later version.
*
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  You should have received a copy of the GNU General Public License
*  along with this program; if not, write to the Free Software
*  Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*
*=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=
*/

/**
 * @package xmlnuke
 */
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
	*@access public
	*@return int
	*/
	public function Count()
	{
		return sqlrcur_rowCount($this->_cursor);
	}

	/**
	*@access public
	*@return bool
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
		else if ($this->_currentRow < $this->Count())
		{
			$sr = new SingleRow();

      		for ($col=0; $col<sqlrcur_colCount($this->_cursor); $col++)
      		{
      			$fieldName = strtolower(sqlrcur_getColumnName($this->_cursor, $col) );
      			$value = sqlrcur_getField($this->_cursor, $this->_currentRow, $col);

      			if (is_null($value))
      			{
      				$sr->AddField($fieldName, "");
      			}
      			elseif (is_object($value))
      			{
      				$sr->AddField($fieldName, "[OBJECT]");
      			}
				else
				{
					$value = AnyDataset::fixUTF8($value);
					$sr->AddField($fieldName, $value);
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

            //if (this._db != null)
            //{
            //    this._db.Close();
            //    this._db = null;
            //}
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
	*@access public
	*@return SingleRow
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
?>
